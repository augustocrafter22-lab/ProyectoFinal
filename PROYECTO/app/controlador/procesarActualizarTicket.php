<?php

/**
 * Controlador (endpoint JSON) que actualiza estado y prioridad de un ticket.
 *
 * Requiere sesión de técnico activa y una solicitud POST con "idTicket",
 * "estado" (uno de Pendiente/En Proceso/Resuelto/Cerrado) y "prioridad"
 * (uno de Indefinida/Alta/Media/Baja). Responde con un JSON indicando
 * éxito o el error correspondiente.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";

header("Content-Type: application/json");

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    http_response_code(403);
    echo json_encode(["exito" => false, "mensaje" => "No tiene autorización para realizar esta acción."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
    exit;
}

$idTicket = trim($_POST["idTicket"] ?? "");
$estado = trim($_POST["estado"] ?? "");
$prioridad = trim($_POST["prioridad"] ?? "");

$estadosValidos = ["Pendiente", "En Proceso", "Resuelto", "Cerrado"];
$prioridadesValidas = ["Indefinida", "Alta", "Media", "Baja"];

if ($idTicket === "" || !in_array($estado, $estadosValidos, true) || !in_array($prioridad, $prioridadesValidas, true)) {
    http_response_code(400);
    echo json_encode(["exito" => false, "mensaje" => "Datos inválidos."]);
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $accesoDatosTicket->actualizarEstadoYPrioridad($idTicket, $estado, $prioridad);

    $conectorPDO->desconectar();

    echo json_encode(["exito" => true, "mensaje" => "Ticket actualizado correctamente."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["exito" => false, "mensaje" => "Error: " . $e->getMessage()]);
}

?>
