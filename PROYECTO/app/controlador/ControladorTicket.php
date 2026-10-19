<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOTicket.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de TICKET.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/index.php?ruta=tickets         lista todos los tickets
 *   GET    /public/index.php?ruta=tickets&id=INC- devuelve un ticket puntual
 *   POST   /public/index.php?ruta=tickets         registra un ticket nuevo
 *   PUT    /public/index.php?ruta=tickets&id=INC- actualiza estado y prioridad
 *   DELETE /public/index.php?ruta=tickets&id=INC- elimina un ticket
 */
class ControladorTicket
{
    private DAOTicket $dao;

    /** @var array Estados permitidos para un ticket. */
    private array $estadosValidos = ["Pendiente", "En Proceso", "Resuelto", "Cerrado"];

    /** @var array Prioridades permitidas para un ticket. */
    private array $prioridadesValidas = ["Indefinida", "Alta", "Media", "Baja"];

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // El docente solo puede ingresar tickets, el resto lo hace el técnico.
        if ($metodo === "POST") {
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

        $this->dao = new DAOTicket($conexion);
    }

    /**
     * Devuelve el listado de tickets, o uno puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el ticket solicitado no existe.
     */
    private function listar(): array
    {
        $idTicket = trim($_GET["id"] ?? "");

        if ($idTicket !== "") {
            $ticket = $this->dao->obtener($idTicket);

            if ($ticket === null) {
                throw new Exception("No existe un ticket con el identificador $idTicket.", 404);
            }

            return ["datos" => $ticket, "mensaje" => "Ticket encontrado.", "codigo" => 200];
        }

        $filtros = $this->obtenerFiltros();

        return ["datos" => $this->dao->listar($filtros), "mensaje" => "Listado de tickets.", "codigo" => 200];
    }

    /**
     * Lee y valida los filtros opcionales enviados por GET.
     *
     * @return array Arreglo con las claves estado, prioridad, fechaDesde,
     *               fechaHasta y busqueda.
     * @throws Exception Si estado o prioridad traen un valor no permitido.
     */
    private function obtenerFiltros(): array
    {
        $estado = trim($_GET["estado"] ?? "");
        $prioridad = trim($_GET["prioridad"] ?? "");

        if ($estado !== "" && !in_array($estado, $this->estadosValidos, true)) {
            throw new Exception("El filtro estado tiene un valor no permitido.", 400);
        }

        if ($prioridad !== "" && !in_array($prioridad, $this->prioridadesValidas, true)) {
            throw new Exception("El filtro prioridad tiene un valor no permitido.", 400);
        }

        return [
            "estado" => $estado,
            "prioridad" => $prioridad,
            "fechaDesde" => trim($_GET["fechaDesde"] ?? ""),
            "fechaHasta" => trim($_GET["fechaHasta"] ?? ""),
            "busqueda" => trim($_GET["q"] ?? "")
        ];
    }

    /**
     * Registra un ticket nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si falta algún campo obligatorio o el equipo no es del laboratorio indicado.
     */
    private function registrar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $datos = [
            "laboratorio" => Validador::longitud($datosEnviados["laboratorio"] ?? "", 1, 20, "laboratorio"),
            "equipo" => Validador::longitud($datosEnviados["equipo"] ?? "", 1, 10, "equipo"),
            "asunto" => Validador::longitud($datosEnviados["asunto"] ?? "", 1, 100, "asunto"),
            "descripcion" => Validador::requerido($datosEnviados["descripcion"] ?? "", "descripcion"),
            "turno" => Validador::longitud($datosEnviados["turno"] ?? "", 1, 15, "turno"),
            "grupo" => Validador::longitud($datosEnviados["grupo"] ?? "", 1, 10, "grupo"),
            "profesor" => Validador::longitud($datosEnviados["profesor"] ?? "", 1, 50, "profesor")
        ];

        // El equipo tiene que estar en el laboratorio donde se reportó la incidencia.
        if (!$this->dao->equipoEstaEnLaboratorio($datos["equipo"], $datos["laboratorio"])) {
            throw new Exception("El equipo " . $datos["equipo"] . " no pertenece al laboratorio " . $datos["laboratorio"] . ".", 400);
        }

        $idTicket = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idTicket),
            "mensaje" => "Ticket $idTicket registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza el estado y la prioridad de un ticket existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o el ticket no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $idTicket = Validador::requerido($_GET["id"] ?? ($datosEnviados["idTicket"] ?? ""), "identificador del ticket");
        $estado = Validador::enLista($datosEnviados["estado"] ?? "", $this->estadosValidos, "estado");
        $prioridad = Validador::enLista($datosEnviados["prioridad"] ?? "", $this->prioridadesValidas, "prioridad");

        if ($this->dao->obtener($idTicket) === null) {
            throw new Exception("No existe un ticket con el identificador $idTicket.", 404);
        }

        $this->dao->actualizar($idTicket, ["estado" => $estado, "prioridad" => $prioridad]);

        return [
            "datos" => $this->dao->obtener($idTicket),
            "mensaje" => "Ticket $idTicket actualizado correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina un ticket existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o el ticket no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();

        $idTicket = trim($_GET["id"] ?? ($datosEnviados["idTicket"] ?? ""));

        if ($idTicket === "") {
            throw new Exception("Debe indicar el identificador del ticket.", 400);
        }

        if ($this->dao->obtener($idTicket) === null) {
            throw new Exception("No existe un ticket con el identificador $idTicket.", 404);
        }

        $this->dao->eliminar($idTicket);

        return ["datos" => null, "mensaje" => "Ticket $idTicket eliminado correctamente.", "codigo" => 200];
    }

}

?>