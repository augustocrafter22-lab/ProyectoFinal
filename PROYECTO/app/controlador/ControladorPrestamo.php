<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOPrestamo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de PRESTAMO.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/api/prestamos.php                       lista todos los préstamos
 *   GET    /public/api/prestamos.php?id=3                  devuelve un préstamo puntual
 *   GET    /public/api/prestamos.php?estado=Activo&equipo=PC-01
 *   POST   /public/api/prestamos.php                       registra un préstamo nuevo
 *   PUT    /public/api/prestamos.php?id=3                  registra la devolución o cambia la fecha estimada
 *   DELETE /public/api/prestamos.php?id=3                  elimina un préstamo ya devuelto
 *
 * Al registrar un préstamo el equipo pasa a "No disponible", y al devolverlo
 * vuelve a "Disponible" (si está funcionando). En POST se envían idEquipo,
 * cedulaSolicitante y fechaDevolucionEstimada (AAAA-MM-DD, hasta el final de ese día).
 * En PUT se envía estado "Devuelto" para registrar la devolución y/o una nueva
 * fechaDevolucionEstimada.
 *
 * Todo el endpoint es del coordinador y del técnico.
 */
class ControladorPrestamo
{
    private ConectorPDO $conectorPDO;
    private DAOPrestamo $dao;

    /** @var array Estados permitidos para un préstamo. */
    private array $estadosValidos = ["Activo", "Devuelto"];

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        Sesion::verificarRolApi(["coordinador", "tecnico"]);

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

        $this->dao = new DAOPrestamo($conexion);
    }

    /**
     * Devuelve el listado de préstamos, o uno puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el id es inválido, un filtro no es válido o el préstamo no existe.
     */
    private function listar(): array
    {
        $id = Validador::limpiar($_GET["id"] ?? "");

        if ($id !== "") {
            $idPrestamo = (int) Validador::numerico($id, "id");
            $prestamo = $this->dao->obtener($idPrestamo);

            if ($prestamo === null) {
                throw new Exception("No existe un préstamo con el identificador $idPrestamo.", 404);
            }

            return ["datos" => $prestamo, "mensaje" => "Préstamo encontrado.", "codigo" => 200];
        }

        return [
            "datos" => $this->dao->listar($this->obtenerFiltros()),
            "mensaje" => "Listado de préstamos.",
            "codigo" => 200
        ];
    }

    /**
     * Lee y valida los filtros opcionales enviados por GET.
     *
     * @return array Arreglo con las claves opcionales estado e idEquipo.
     * @throws Exception Si el estado trae un valor no permitido.
     */
    private function obtenerFiltros(): array
    {
        $filtros = [];

        $estado = Validador::limpiar($_GET["estado"] ?? "");
        $equipo = Validador::limpiar($_GET["equipo"] ?? "");

        if ($estado !== "") {
            $filtros["estado"] = Validador::enLista($estado, $this->estadosValidos, "estado");
        }

        if ($equipo !== "") {
            $filtros["idEquipo"] = Validador::longitud($equipo, 1, 10, "equipo");
        }

        return $filtros;
    }

    /**
     * Registra un préstamo nuevo. El equipo debe existir y estar disponible.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o el equipo no está disponible.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idEquipo = Validador::longitud($datosEnviados["idEquipo"] ?? "", 1, 10, "idEquipo");
        $cedula = Validador::cedula($datosEnviados["cedulaSolicitante"] ?? "");
        $fechaEstimada = $this->validarFechaEstimada($datosEnviados["fechaDevolucionEstimada"] ?? "");

        if (!$this->dao->existeEquipo($idEquipo)) {
            throw new Exception("No existe el equipo $idEquipo.", 400);
        }

        if (!$this->dao->existeUsuario($cedula)) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 400);
        }

        $idPrestamo = $this->dao->crear([
            "idEquipo" => $idEquipo,
            "cedulaSolicitante" => $cedula,
            "fechaDevolucionEstimada" => $fechaEstimada
        ]);

        if ($idPrestamo === 0) {
            throw new Exception("El equipo $idEquipo no está disponible para prestar.", 409);
        }

        return [
            "datos" => $this->dao->obtener($idPrestamo),
            "mensaje" => "Préstamo $idPrestamo registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Modifica un préstamo activo: registra su devolución y/o cambia la fecha
     * de devolución estimada.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se envía nada para modificar, el préstamo no existe
     *                   o ya fue devuelto.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idPrestamo = $this->obtenerIdIndicado($datosEnviados);
        $prestamo = $this->dao->obtener($idPrestamo);

        if ($prestamo === null) {
            throw new Exception("No existe un préstamo con el identificador $idPrestamo.", 404);
        }

        $quiereDevolver = array_key_exists("estado", $datosEnviados);
        $quiereCambiarFecha = array_key_exists("fechaDevolucionEstimada", $datosEnviados);

        if (!$quiereDevolver && !$quiereCambiarFecha) {
            throw new Exception("Debe enviar el estado o la fechaDevolucionEstimada para modificar.", 400);
        }

        if ($quiereDevolver) {
            // El único cambio de estado permitido es devolver el equipo.
            Validador::enLista($datosEnviados["estado"], ["Devuelto"], "estado");
        }

        if ($quiereCambiarFecha) {
            $fechaEstimada = $this->validarFechaEstimada($datosEnviados["fechaDevolucionEstimada"]);
        }

        if ($prestamo["estado"] !== "Activo") {
            throw new Exception("El préstamo $idPrestamo ya fue devuelto.", 409);
        }

        if ($quiereCambiarFecha) {
            $this->dao->actualizarFechaEstimada($idPrestamo, $fechaEstimada);
        }

        $mensaje = "Préstamo $idPrestamo actualizado correctamente.";

        if ($quiereDevolver) {
            $this->dao->devolver($idPrestamo);
            $mensaje = "Devolución del préstamo $idPrestamo registrada correctamente.";
        }

        return ["datos" => $this->dao->obtener($idPrestamo), "mensaje" => $mensaje, "codigo" => 200];
    }

    /**
     * Elimina un préstamo existente. Un préstamo activo no se puede eliminar,
     * primero hay que registrar la devolución para que el equipo quede disponible.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id, el préstamo no existe o sigue activo.
     */
    private function eliminar(): array
    {
        $idPrestamo = $this->obtenerIdIndicado($this->obtenerDatosEnviados());
        $prestamo = $this->dao->obtener($idPrestamo);

        if ($prestamo === null) {
            throw new Exception("No existe un préstamo con el identificador $idPrestamo.", 404);
        }

        if ($prestamo["estado"] === "Activo") {
            throw new Exception("El préstamo $idPrestamo sigue activo, registre primero la devolución.", 409);
        }

        $this->dao->eliminar($idPrestamo);

        return ["datos" => null, "mensaje" => "Préstamo $idPrestamo eliminado correctamente.", "codigo" => 200];
    }

    /**
     * Obtiene el id del préstamo sobre el que se opera, que llega por
     * la URL (?id=) o dentro de los datos enviados.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return int El id del préstamo, ya validado.
     * @throws Exception Si no se indicó el id o no es un número.
     */
    private function obtenerIdIndicado(array $datosEnviados): int
    {
        $id = Validador::limpiar($_GET["id"] ?? ($datosEnviados["idPrestamo"] ?? ""));

        if ($id === "") {
            throw new Exception("Debe indicar el identificador del préstamo.", 400);
        }

        return (int) Validador::numerico($id, "id");
    }

    /**
     * Valida la fecha de devolución estimada: debe ser una fecha real y no
     * puede ser anterior a hoy. Se guarda hasta el final de ese día.
     *
     * @param mixed $fecha Fecha recibida del cliente (AAAA-MM-DD).
     * @return string La fecha con hora 23:59:59, lista para guardar.
     * @throws Exception Si la fecha es inválida o ya pasó.
     */
    private function validarFechaEstimada($fecha): string
    {
        $fecha = Validador::fecha($fecha, "fechaDevolucionEstimada");

        // Las fechas con formato AAAA-MM-DD se pueden comparar como texto.
        if ($fecha < date("Y-m-d")) {
            throw new Exception("La fechaDevolucionEstimada no puede ser anterior a hoy.", 400);
        }

        return $fecha . " 23:59:59";
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
