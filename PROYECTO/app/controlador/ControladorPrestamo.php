<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOPrestamo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador unificado de PRESTAMO.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/index.php?ruta=prestamos                       lista todos los préstamos
 *   GET    /public/index.php?ruta=prestamos&id=3                  devuelve un préstamo puntual
 *   GET    /public/index.php?ruta=prestamos&estado=Activo&equipo=PC-01
 *   POST   /public/index.php?ruta=prestamos                       registra un préstamo nuevo
 *   PUT    /public/index.php?ruta=prestamos&id=3                  registra la devolución o cambia la fecha estimada
 *   DELETE /public/index.php?ruta=prestamos&id=3                  elimina un préstamo ya devuelto
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
                throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
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
                throw new Exception(Traductor::t("prestamos.noExiste", ["id" => $idPrestamo]), 404);
            }

            return ["datos" => $prestamo, "mensaje" => Traductor::t("prestamos.encontrado"), "codigo" => 200];
        }

        return [
            "datos" => $this->dao->listar($this->obtenerFiltros()),
            "mensaje" => Traductor::t("prestamos.listado"),
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
        $datosEnviados = Solicitud::obtenerDatos();

        $idEquipo = Validador::longitud($datosEnviados["idEquipo"] ?? "", 1, 10, "idEquipo");
        $cedula = Validador::cedula($datosEnviados["cedulaSolicitante"] ?? "");
        $fechaEstimada = $this->validarFechaEstimada($datosEnviados["fechaDevolucionEstimada"] ?? "");

        if (!$this->dao->existeEquipo($idEquipo)) {
            throw new Exception(Traductor::t("equipos.noExiste", ["id" => $idEquipo]), 400);
        }

        if (!$this->dao->existeUsuario($cedula)) {
            throw new Exception(Traductor::t("usuarios.noExiste", ["cedula" => $cedula]), 400);
        }

        $idPrestamo = $this->dao->crear([
            "idEquipo" => $idEquipo,
            "cedulaSolicitante" => $cedula,
            "fechaDevolucionEstimada" => $fechaEstimada
        ]);

        if ($idPrestamo === 0) {
            throw new Exception(Traductor::t("prestamos.equipoNoDisponible", ["id" => $idEquipo]), 409);
        }

        return [
            "datos" => $this->dao->obtener($idPrestamo),
            "mensaje" => Traductor::t("prestamos.registrado", ["id" => $idPrestamo]),
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
        $datosEnviados = Solicitud::obtenerDatos();

        $idPrestamo = $this->obtenerIdIndicado($datosEnviados);
        $prestamo = $this->dao->obtener($idPrestamo);

        if ($prestamo === null) {
            throw new Exception(Traductor::t("prestamos.noExiste", ["id" => $idPrestamo]), 404);
        }

        $quiereDevolver = array_key_exists("estado", $datosEnviados);
        $quiereCambiarFecha = array_key_exists("fechaDevolucionEstimada", $datosEnviados);

        if (!$quiereDevolver && !$quiereCambiarFecha) {
            throw new Exception(Traductor::t("prestamos.sinCambios"), 400);
        }

        if ($quiereDevolver) {
            // El único cambio de estado permitido es devolver el equipo.
            Validador::enLista($datosEnviados["estado"], ["Devuelto"], "estado");
        }

        if ($quiereCambiarFecha) {
            $fechaEstimada = $this->validarFechaEstimada($datosEnviados["fechaDevolucionEstimada"]);
        }

        if ($prestamo["estado"] !== "Activo") {
            throw new Exception(Traductor::t("prestamos.yaDevuelto", ["id" => $idPrestamo]), 409);
        }

        if ($quiereCambiarFecha) {
            $this->dao->actualizarFechaEstimada($idPrestamo, $fechaEstimada);
        }

        $mensaje = Traductor::t("prestamos.actualizado", ["id" => $idPrestamo]);

        if ($quiereDevolver) {
            $this->dao->devolver($idPrestamo);
            $mensaje = Traductor::t("prestamos.devuelto", ["id" => $idPrestamo]);
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
        $idPrestamo = $this->obtenerIdIndicado(Solicitud::obtenerDatos());
        $prestamo = $this->dao->obtener($idPrestamo);

        if ($prestamo === null) {
            throw new Exception(Traductor::t("prestamos.noExiste", ["id" => $idPrestamo]), 404);
        }

        if ($prestamo["estado"] === "Activo") {
            throw new Exception(Traductor::t("prestamos.sigueActivo", ["id" => $idPrestamo]), 409);
        }

        $this->dao->eliminar($idPrestamo);

        return ["datos" => null, "mensaje" => Traductor::t("prestamos.eliminado", ["id" => $idPrestamo]), "codigo" => 200];
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
            throw new Exception(Traductor::t("prestamos.faltaId"), 400);
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
            throw new Exception(Traductor::t("prestamos.fechaPasada"), 400);
        }

        return $fecha . " 23:59:59";
    }

}

?>
