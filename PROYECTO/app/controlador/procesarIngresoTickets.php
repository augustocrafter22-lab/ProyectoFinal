<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";

session_start();

if (!isset($_SESSION["cedula"]) || !($_SESSION["docente"] ?? false)) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como docente."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php");
    exit;
}

$laboratorio = trim($_POST["laboratorio"] ?? "");
$equipo = trim($_POST["equipo"] ?? "");
$asunto = trim($_POST["asunto"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$turno = trim($_POST["turno"] ?? "");
$grupo = trim($_POST["grupo"] ?? "");
$profesor = trim($_POST["profesor"] ?? "");

if ($laboratorio === "" || $equipo === "" || $asunto === "" || $descripcion === ""
    || $turno === "" || $grupo === "" || $profesor === "") {
    $mensaje = "Debe completar todos los campos del ticket.";
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php?error=" . urlencode($mensaje));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $idTicket = $accesoDatosTicket->registrarTicket($laboratorio, $equipo, $asunto, $descripcion, $turno, $grupo, $profesor);

    $conectorPDO->desconectar();

    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php?exito=" . urlencode("Ticket $idTicket registrado correctamente."));
    exit;

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}

?>