<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOLaboratorio.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de LABORATORIO.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/api/laboratorios.php           lista todos los laboratorios
 *   GET    /public/api/laboratorios.php?id=LAB001 devuelve un laboratorio puntual
 *   POST   /public/api/laboratorios.php           registra un laboratorio nuevo
 *   PUT    /public/api/laboratorios.php?id=LAB001 modifica un laboratorio existente
 *   DELETE /public/api/laboratorios.php?id=LAB001 elimina un laboratorio
 *
 * Datos del laboratorio: idLaboratorio, numeroLaboratorio y estado (true o false).
 * Si en POST no se envía estado queda en true, y si en PUT no se envía se conserva
 * el que ya tenía.
 */
class ControladorLaboratorio
{
    private ConectorPDO $conectorPDO;
    private DAOLaboratorio $dao;

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // El docente solo consulta los laboratorios para elegir uno al solicitarlo.
        if ($metodo === "GET") {
            Sesion::verificarRolApi(["coordinador", "tecnico", "docente"]);
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
            RegistradorErrores::registrar($e);

            // 23000 es el código SQLSTATE de una restricción de clave foránea:
            // el laboratorio tiene equipos, tickets o solicitudes y la base no deja borrarlo.
            if ($metodo === "DELETE" && (string) $e->getCode() === "23000") {
                RespuestaJson::error("No se puede eliminar el laboratorio porque tiene registros relacionados.", 409);
            }

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

        $this->dao = new DAOLaboratorio($conexion);
    }

    /**
     * Devuelve el listado de laboratorios, o uno puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el laboratorio solicitado no existe.
     */
    private function listar(): array
    {
        $idLaboratorio = Validador::limpiar($_GET["id"] ?? "");

        if ($idLaboratorio !== "") {
            $laboratorio = $this->dao->obtener($idLaboratorio);

            if ($laboratorio === null) {
                throw new Exception("No existe un laboratorio con el identificador $idLaboratorio.", 404);
            }

            return ["datos" => $laboratorio, "mensaje" => "Laboratorio encontrado.", "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de laboratorios.", "codigo" => 200];
    }

    /**
     * Registra un laboratorio nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o el laboratorio ya existe.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idLaboratorio = Validador::longitud($datosEnviados["idLaboratorio"] ?? "", 1, 20, "idLaboratorio");

        if ($this->dao->obtener($idLaboratorio) !== null) {
            throw new Exception("Ya existe un laboratorio con el identificador $idLaboratorio.", 409);
        }

        $datos = $this->validarDatos($datosEnviados, 1);
        $datos["idLaboratorio"] = $idLaboratorio;

        $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idLaboratorio),
            "mensaje" => "Laboratorio $idLaboratorio registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza los datos de un laboratorio existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o el laboratorio no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idLaboratorio = $this->obtenerIdIndicado($datosEnviados);
        $laboratorioActual = $this->dao->obtener($idLaboratorio);

        if ($laboratorioActual === null) {
            throw new Exception("No existe un laboratorio con el identificador $idLaboratorio.", 404);
        }

        // Si no se envía estado, se conserva el que tenía el laboratorio.
        $datos = $this->validarDatos($datosEnviados, (int) $laboratorioActual["estado"]);

        $this->dao->actualizar($idLaboratorio, $datos);

        return [
            "datos" => $this->dao->obtener($idLaboratorio),
            "mensaje" => "Laboratorio $idLaboratorio actualizado correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina un laboratorio existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o el laboratorio no existe.
     */
    private function eliminar(): array
    {
        $idLaboratorio = $this->obtenerIdIndicado($this->obtenerDatosEnviados());

        if ($this->dao->obtener($idLaboratorio) === null) {
            throw new Exception("No existe un laboratorio con el identificador $idLaboratorio.", 404);
        }

        $this->dao->eliminar($idLaboratorio);

        return ["datos" => null, "mensaje" => "Laboratorio $idLaboratorio eliminado correctamente.", "codigo" => 200];
    }

    /**
     * Obtiene el id del laboratorio sobre el que se opera, que llega por
     * la URL (?id=) o dentro de los datos enviados.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return string El id del laboratorio, ya validado.
     * @throws Exception Si no se indicó el id.
     */
    private function obtenerIdIndicado(array $datosEnviados): string
    {
        $idLaboratorio = Validador::limpiar($_GET["id"] ?? ($datosEnviados["idLaboratorio"] ?? ""));

        if ($idLaboratorio === "") {
            throw new Exception("Debe indicar el identificador del laboratorio.", 400);
        }

        return Validador::longitud($idLaboratorio, 1, 20, "idLaboratorio");
    }

    /**
     * Valida los datos de alta y de modificación de un laboratorio.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @param int $estadoPorDefecto Estado a usar si el cliente no envía uno (1 o 0).
     * @return array Arreglo con numeroLaboratorio y estado ya validados.
     * @throws Exception Si falta el número o el estado no es true o false.
     */
    private function validarDatos(array $datosEnviados, int $estadoPorDefecto): array
    {
        $estado = $estadoPorDefecto;

        if (array_key_exists("estado", $datosEnviados)) {
            $estado = Validador::booleano($datosEnviados["estado"], "estado");
        }

        return [
            "numeroLaboratorio" => Validador::longitud($datosEnviados["numeroLaboratorio"] ?? "", 1, 10, "numeroLaboratorio"),
            "estado" => $estado
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
