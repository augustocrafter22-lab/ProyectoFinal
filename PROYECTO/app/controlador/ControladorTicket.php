<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOTicket.php";
require_once RUTA_VISTA . "/RespuestaJson.php";

/**
 * Controlador unificado de TICKET.
 *
 * Agrupa las funcionalidades que antes estaban sueltas en varios
 * controladores (cargarTickets, procesarIngresoTickets,
 * procesarActualizarTicket) en una sola clase.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/api/tickets.php         lista todos los tickets
 *   GET    /public/api/tickets.php?id=INC- devuelve un ticket puntual
 *   POST   /public/api/tickets.php         registra un ticket nuevo
 *   PUT    /public/api/tickets.php?id=INC- actualiza estado y prioridad
 *   DELETE /public/api/tickets.php?id=INC- elimina un ticket
 */
class ControladorTicket
{
    private ConectorPDO $conectorPDO;
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
        try {
            $this->conectar();

            switch ($_SERVER["REQUEST_METHOD"]) {
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

        } catch (Exception $e) {
            // Si el código de la excepción es un código HTTP válido se usa ese,
            // en cualquier otro caso se responde con un 500 (error del servidor).
            $codigo = $e->getCode() >= 400 && $e->getCode() <= 599 ? (int) $e->getCode() : 500;
            RespuestaJson::error($e->getMessage(), $codigo);
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

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de tickets.", "codigo" => 200];
    }

    /**
     * Registra un ticket nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si falta algún campo obligatorio.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $campos = ["laboratorio", "equipo", "asunto", "descripcion", "turno", "grupo", "profesor"];
        $datos = [];

        foreach ($campos as $campo) {
            $valor = trim($datosEnviados[$campo] ?? "");

            if ($valor === "") {
                throw new Exception("El campo $campo es obligatorio.", 400);
            }

            $datos[$campo] = $valor;
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
        $datosEnviados = $this->obtenerDatosEnviados();

        $idTicket = trim($_GET["id"] ?? ($datosEnviados["idTicket"] ?? ""));
        $estado = trim($datosEnviados["estado"] ?? "");
        $prioridad = trim($datosEnviados["prioridad"] ?? "");

        if ($idTicket === "") {
            throw new Exception("Debe indicar el identificador del ticket.", 400);
        }

        if (!in_array($estado, $this->estadosValidos, true)) {
            throw new Exception("El estado indicado no es válido.", 400);
        }

        if (!in_array($prioridad, $this->prioridadesValidas, true)) {
            throw new Exception("La prioridad indicada no es válida.", 400);
        }

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
        $datosEnviados = $this->obtenerDatosEnviados();

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

        // Si no vino JSON, se intenta con un formulario POST
        // o con el formato clave=valor del cuerpo PUT y DELETE.
        if (!empty($_POST)) {
            return $_POST;
        }

        parse_str($cuerpo, $datosFormulario);

        return is_array($datosFormulario) ? $datosFormulario : [];
    }
}

?>