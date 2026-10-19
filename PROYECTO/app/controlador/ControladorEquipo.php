<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOEquipo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador unificado de EQUIPO.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/index.php?ruta=equipos            lista todos los equipos
 *   GET    /public/index.php?ruta=equipos&id=PC-01   devuelve un equipo puntual
 *   GET    /public/index.php?ruta=equipos&estado=Funcionando&laboratorio=101&disponibilidad=Disponible
 *   POST   /public/index.php?ruta=equipos            registra un equipo nuevo
 *   PUT    /public/index.php?ruta=equipos&id=PC-01   modifica un equipo existente
 *   DELETE /public/index.php?ruta=equipos&id=PC-01   elimina un equipo
 */
class ControladorEquipo
{
    private DAOEquipo $dao;

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // El docente solo consulta los equipos al ingresar un ticket.
        if ($metodo === "GET") {
            Sesion::verificarRolApi(["coordinador", "tecnico", "docente"]);
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

        $this->dao = new DAOEquipo($conexion);
    }

    /**
     * Devuelve el listado de equipos, o uno especifico si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el equipo solicitado no existe.
     */
    private function listar(): array
    {
        $idEquipo = trim($_GET["id"] ?? "");

        if ($idEquipo !== "") {
            $equipo = $this->dao->obtener($idEquipo);

            if ($equipo === null) {
                throw new Exception(Traductor::t("equipos.noExiste", ["id" => $idEquipo]), 404);
            }

            return ["datos" => $equipo, "mensaje" => Traductor::t("equipos.encontrado"), "codigo" => 200];
        }

        return [
            "datos" => $this->dao->listar($this->obtenerFiltros()),
            "mensaje" => Traductor::t("equipos.listado"),
            "codigo" => 200
        ];
    }

    /**
     * Lee y valida los filtros opcionales enviados por GET.
     *
     * @return array Arreglo con las claves estado, laboratorio y disponibilidad.
     * @throws Exception Si estado o disponibilidad traen un valor no permitido.
     */
    private function obtenerFiltros(): array
    {
        $estado = trim($_GET["estado"] ?? "");
        $laboratorio = trim($_GET["laboratorio"] ?? ($_GET["idLaboratorio"] ?? ""));
        $disponibilidad = trim($_GET["disponibilidad"] ?? "");

        $estadosValidos = ["Dañado", "Funcionando", "En mantenimiento", "No funciona"];
        $disponibilidadesValidas = ["Disponible", "No disponible"];

        if ($estado !== "" && !in_array($estado, $estadosValidos, true)) {
            throw new Exception(Traductor::t("validacion.filtroNoPermitido", ["filtro" => Traductor::t("campos.estado")]), 400);
        }

        if ($disponibilidad !== "" && !in_array($disponibilidad, $disponibilidadesValidas, true)) {
            throw new Exception(Traductor::t("validacion.filtroNoPermitido", ["filtro" => Traductor::t("campos.disponibilidad")]), 400);
        }

        if ($laboratorio !== "") {
            $laboratorio = Validador::longitud($laboratorio, 1, 20, "laboratorio");
        }

        return [
            "estado" => $estado,
            "laboratorio" => $laboratorio,
            "disponibilidad" => $disponibilidad
        ];
    }

    /**
     * Registra un equipo nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el equipo ya existe.
     */
    private function registrar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $idEquipo = Validador::requerido($datosEnviados["idEquipo"] ?? "", "idEquipo");
        $idEquipo = Validador::longitud($idEquipo, 1, 10, "idEquipo");

        if ($this->dao->obtener($idEquipo) !== null) {
            throw new Exception(Traductor::t("equipos.yaExiste", ["id" => $idEquipo]), 409);
        }

        $datos = $this->validarDatos($datosEnviados);
        $datos["idEquipo"] = $idEquipo;

        $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idEquipo),
            "mensaje" => Traductor::t("equipos.registrado", ["id" => $idEquipo]),
            "codigo" => 201
        ];
    }

    /**
     * Actualiza los datos de un equipo existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el equipo no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $idEquipo = trim($_GET["id"] ?? ($datosEnviados["idEquipo"] ?? ""));

        if ($idEquipo === "") {
            throw new Exception(Traductor::t("equipos.faltaId"), 400);
        }

        $idEquipo = Validador::longitud($idEquipo, 1, 10, "idEquipo");

        if ($this->dao->obtener($idEquipo) === null) {
            throw new Exception(Traductor::t("equipos.noExiste", ["id" => $idEquipo]), 404);
        }

        $datos = $this->validarDatos($datosEnviados);

        $this->dao->actualizar($idEquipo, $datos, $_SESSION["cedula"]);

        return [
            "datos" => $this->dao->obtener($idEquipo),
            "mensaje" => Traductor::t("equipos.actualizado", ["id" => $idEquipo]),
            "codigo" => 200
        ];
    }

    /**
     * Elimina un equipo existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o el equipo no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $idEquipo = trim($_GET["id"] ?? ($datosEnviados["idEquipo"] ?? ""));

        if ($idEquipo === "") {
            throw new Exception(Traductor::t("equipos.faltaId"), 400);
        }

        $idEquipo = Validador::longitud($idEquipo, 1, 10, "idEquipo");

        if ($this->dao->obtener($idEquipo) === null) {
            throw new Exception(Traductor::t("equipos.noExiste", ["id" => $idEquipo]), 404);
        }

        $this->dao->eliminar($idEquipo);

        return ["datos" => null, "mensaje" => Traductor::t("equipos.eliminado", ["id" => $idEquipo]), "codigo" => 200];
    }

    /**
     * Valida los datos de alta y la modificación de un equipo.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el laboratorio no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $datos = [
            "idLaboratorio" => Validador::longitud(
                Validador::requerido($datosEnviados["idLaboratorio"] ?? "", "idLaboratorio"),
                1,
                20,
                "idLaboratorio"
            ),
            "marca" => Validador::longitud($datosEnviados["marca"] ?? "", 1, 30, "marca"),
            "estado" => Validador::enLista($datosEnviados["estado"] ?? "", ["Dañado", "Funcionando", "En mantenimiento", "No funciona"], "estado"),
            "disponibilidad" => Validador::enLista($datosEnviados["disponibilidad"] ?? "", ["Disponible", "No disponible"], "disponibilidad")
        ];

        if (!$this->dao->existeLaboratorio($datos["idLaboratorio"])) {
            throw new Exception(Traductor::t("laboratorios.noExiste", ["id" => $datos["idLaboratorio"]]), 400);
        }

        $datos["informacion"] = trim($datosEnviados["informacion"] ?? "");

        return $datos;
    }

}

?>