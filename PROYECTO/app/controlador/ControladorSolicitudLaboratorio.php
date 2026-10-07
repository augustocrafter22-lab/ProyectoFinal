<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOSolicitudLaboratorio.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de SOLICITUD_LABORATORIO.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/api/solicitudes.php                lista todas las solicitudes
 *   GET    /public/api/solicitudes.php?id=3           devuelve una solicitud puntual
 *   GET    /public/api/solicitudes.php?tipo=software&laboratorio=LAB001&fecha=2026-10-20
 *   POST   /public/api/solicitudes.php                registra una solicitud nueva
 *   PUT    /public/api/solicitudes.php?id=3           modifica una solicitud existente
 *   DELETE /public/api/solicitudes.php?id=3           elimina una solicitud
 *
 * El filtro "tipo" separa las solicitudes de preparación de un laboratorio
 * (tipo=preparacion) de las de instalación de software (tipo=software).
 *
 * Solo el docente puede registrar una solicitud, y queda a su nombre.
 * El coordinador y el técnico las consultan, modifican y eliminan.
 */
class ControladorSolicitudLaboratorio
{
    private ConectorPDO $conectorPDO;
    private DAOSolicitudLaboratorio $dao;

    /** @var array Tipos de solicitud que se pueden usar como filtro. */
    private array $tiposValidos = ["preparacion", "software"];

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // La solicitud queda a nombre del docente que la hace, por eso solo él puede registrarla.
        if ($metodo === "POST") {
            Sesion::verificarRolApi(["docente"]);
        } else {
            Sesion::verificarRolApi(["coordinador", "tecnico"]);
        }

        // GET no modifica datos, por eso solo se pide el token en POST, PUT y DELETE.
        if (in_array($metodo, ["POST", "PUT", "DELETE"], true)) {
            Token::verificarCSRF();
        }

        try {
            $this->conectar();

            switch ($metodo) {
                case "GET":
                    $resultado = $this->listar();
                    break;
                case "POST":
                    $resultado = $this->registrar();
                    break;
                case "PUT":
                    $resultado = $this->actualizar();
                    break;
                case "DELETE":
                    $resultado = $this->eliminar();
                    break;
                default:
                    throw new Exception("Método no permitido.", 405);
            }

            $this->conectorPDO->desconectar();

            RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);

        } catch (PDOException $e) {
            // Los errores de la base de datos traen un código SQLSTATE (por ejemplo "42S02"),
            // que no es un código HTTP, por eso siempre se responde con un 500.
            RegistradorErrores::registrar($e);
            RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
        } catch (Exception $e) {
            // Si el código de la excepción es un código HTTP válido se usa ese,
            // en cualquier otro caso se responde con un 500 (error del servidor).
            $codigo = $e->getCode() >= 400 && $e->getCode() <= 599 ? (int) $e->getCode() : 500;

            if ($codigo === 500) {
                RegistradorErrores::registrar($e);
                RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
            } else {
                RespuestaJson::error($e->getMessage(), $codigo);
            }
        }
    }

    /**
     * Establece la conexión con la base de datos y crea el DAO.
     *
     * @return void
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    private function conectar(): void
    {
        $this->conectorPDO = new ConectorPDO($_ENV["BD_HOST"], $_ENV["BD_USER"], $_ENV["BD_PASS"], $_ENV["BD_NAME"]);
        $conexion = $this->conectorPDO->establecerConexion();

        if ($conexion === null) {
            throw new Exception("No se pudo conectar a la base de datos.", 500);
        }

        $this->dao = new DAOSolicitudLaboratorio($conexion);
    }

    /**
     * Devuelve el listado de solicitudes, o una puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el id es inválido o la solicitud no existe.
     */
    private function listar(): array
    {
        $id = Validador::limpiar($_GET["id"] ?? "");

        if ($id !== "") {
            $idSolicitud = (int) Validador::numerico($id, "id");
            $solicitud = $this->dao->obtener($idSolicitud);

            if ($solicitud === null) {
                throw new Exception("No existe una solicitud con el identificador $idSolicitud.", 404);
            }

            return ["datos" => $solicitud, "mensaje" => "Solicitud encontrada.", "codigo" => 200];
        }

        return [
            "datos" => $this->dao->listar($this->obtenerFiltros()),
            "mensaje" => "Listado de solicitudes.",
            "codigo" => 200
        ];
    }

    /**
     * Lee y valida los filtros opcionales enviados por GET.
     *
     * @return array Arreglo con las claves opcionales solicitaSoftware,
     *               idLaboratorio y fechaEstimada.
     * @throws Exception Si algún filtro trae un valor no permitido.
     */
    private function obtenerFiltros(): array
    {
        $filtros = [];

        $tipo = Validador::limpiar($_GET["tipo"] ?? "");
        $laboratorio = Validador::limpiar($_GET["laboratorio"] ?? "");
        $fecha = Validador::limpiar($_GET["fecha"] ?? "");

        // El tipo "software" es una solicitud de instalación de software, el otro es de preparación.
        if ($tipo !== "") {
            $tipo = Validador::enLista($tipo, $this->tiposValidos, "tipo");
            $filtros["solicitaSoftware"] = $tipo === "software" ? 1 : 0;
        }

        if ($laboratorio !== "") {
            $filtros["idLaboratorio"] = Validador::longitud($laboratorio, 1, 20, "laboratorio");
        }

        if ($fecha !== "") {
            $filtros["fechaEstimada"] = Validador::fecha($fecha, "fecha");
        }

        return $filtros;
    }

    /**
     * Registra una solicitud nueva a nombre del docente que inició sesión.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos($this->obtenerDatosEnviados());
        $datos["cedulaSolicitante"] = $_SESSION["cedula"];

        $idSolicitud = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idSolicitud),
            "mensaje" => "Solicitud $idSolicitud registrada correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza los datos de una solicitud existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o la solicitud no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idSolicitud = $this->obtenerIdIndicado($datosEnviados);

        if ($this->dao->obtener($idSolicitud) === null) {
            throw new Exception("No existe una solicitud con el identificador $idSolicitud.", 404);
        }

        $this->dao->actualizar($idSolicitud, $this->validarDatos($datosEnviados));

        return [
            "datos" => $this->dao->obtener($idSolicitud),
            "mensaje" => "Solicitud $idSolicitud actualizada correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina una solicitud existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o la solicitud no existe.
     */
    private function eliminar(): array
    {
        $idSolicitud = $this->obtenerIdIndicado($this->obtenerDatosEnviados());

        if ($this->dao->obtener($idSolicitud) === null) {
            throw new Exception("No existe una solicitud con el identificador $idSolicitud.", 404);
        }

        $this->dao->eliminar($idSolicitud);

        return ["datos" => null, "mensaje" => "Solicitud $idSolicitud eliminada correctamente.", "codigo" => 200];
    }

    /**
     * Obtiene el id de la solicitud sobre la que se opera, que llega por
     * la URL (?id=) o dentro de los datos enviados.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return int El id de la solicitud, ya validado.
     * @throws Exception Si no se indicó el id o no es un número.
     */
    private function obtenerIdIndicado(array $datosEnviados): int
    {
        $id = Validador::limpiar($_GET["id"] ?? ($datosEnviados["idSolicitud"] ?? ""));

        if ($id === "") {
            throw new Exception("Debe indicar el identificador de la solicitud.", 400);
        }

        return (int) Validador::numerico($id, "id");
    }

    /**
     * Valida los datos de alta y de modificación de una solicitud.
     * El detalle solo es obligatorio cuando se solicita software.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el laboratorio no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $idLaboratorio = Validador::longitud($datosEnviados["idLaboratorio"] ?? "", 1, 20, "idLaboratorio");

        if (!$this->dao->existeLaboratorio($idLaboratorio)) {
            throw new Exception("No existe el laboratorio $idLaboratorio.", 400);
        }

        $solicitaSoftware = Validador::booleano($datosEnviados["solicitaSoftware"] ?? "", "solicitaSoftware");

        $detalle = Validador::limpiar($datosEnviados["detalle"] ?? "");
        $restricciones = Validador::limpiar($datosEnviados["restricciones"] ?? "");

        if ($solicitaSoftware === 1) {
            $detalle = Validador::requerido($detalle, "detalle");
        }

        // Las columnas detalle y restricciones aceptan NULL, por eso un texto vacío se guarda como NULL.
        return [
            "idLaboratorio" => $idLaboratorio,
            "solicitaSoftware" => $solicitaSoftware,
            "detalle" => $detalle !== "" ? $detalle : null,
            "restricciones" => $restricciones !== "" ? $restricciones : null,
            "fechaEstimada" => Validador::fecha($datosEnviados["fechaEstimada"] ?? "", "fechaEstimada"),
            "horaEstimada" => Validador::hora($datosEnviados["horaEstimada"] ?? "", "horaEstimada")
        ];
    }

    /**
     * Obtiene los datos enviados por el cliente.
     *
     * Acepta tanto formato JSON como los datos de un formulario $_POST.
     *
     * @return array Arreglo asociativo con los datos recibidos.
     */
    private function obtenerDatosEnviados(): array
    {
        $cuerpo = file_get_contents("php://input");
        $datos = json_decode($cuerpo, true);

        if (is_array($datos)) {
            return $datos;
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        parse_str($cuerpo, $datosFormulario);

        return is_array($datosFormulario) ? $datosFormulario : [];
    }
}

?>
