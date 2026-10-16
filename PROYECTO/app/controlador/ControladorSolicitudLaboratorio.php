<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
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
 *   GET    /public/index.php?ruta=solicitudes                lista todas las solicitudes
 *   GET    /public/index.php?ruta=solicitudes&id=3           devuelve una solicitud puntual
 *   GET    /public/index.php?ruta=solicitudes&tipo=software&laboratorio=LAB001&fecha=2026-10-20
 *   POST   /public/index.php?ruta=solicitudes                registra una solicitud nueva
 *   PUT    /public/index.php?ruta=solicitudes&id=3           modifica una solicitud existente
 *   DELETE /public/index.php?ruta=solicitudes&id=3           elimina una solicitud
 *
 * El filtro "tipo" separa las solicitudes de preparación de un laboratorio
 * (tipo=preparacion) de las de instalación de software (tipo=software).
 *
 * Solo el docente puede registrar una solicitud, y queda a su nombre.
 * El coordinador y el técnico las consultan, modifican y eliminan.
 */
class ControladorSolicitudLaboratorio
{
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

        Conexion::desconectar();

        RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);
    }

    /**
     * Establece la conexión con la base de datos y crea el DAO.
     *
     * @return void
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    private function conectar(): void
    {
        $conexion = Conexion::conectar();

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
        $datos = $this->validarDatos(Solicitud::obtenerDatos());
        $this->validarFechaNoPasada($datos["fechaEstimada"], $datos["horaEstimada"]);
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
        $datosEnviados = Solicitud::obtenerDatos();

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
        $idSolicitud = $this->obtenerIdIndicado(Solicitud::obtenerDatos());

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
     * Verifica que la fecha y hora estimadas de una solicitud nueva no hayan
     * pasado. Solo se aplica al registrar: al modificar, el coordinador o el
     * técnico pueden editar solicitudes cuya fecha ya quedó atrás.
     *
     * @param string $fecha Fecha estimada ya validada (AAAA-MM-DD).
     * @param string $hora Hora estimada ya validada (HH:MM o HH:MM:SS).
     * @return void
     * @throws Exception Si la fecha es anterior a hoy, o es hoy con una hora que ya pasó.
     */
    private function validarFechaNoPasada(string $fecha, string $hora): void
    {
        // Las fechas AAAA-MM-DD y las horas HH:MM se pueden comparar como texto.
        if ($fecha < date("Y-m-d")) {
            throw new Exception("La fechaEstimada no puede ser anterior a hoy.", 400);
        }

        if ($fecha === date("Y-m-d") && substr($hora, 0, 5) < date("H:i")) {
            throw new Exception("La horaEstimada no puede ser anterior a la hora actual.", 400);
        }
    }

}

?>
