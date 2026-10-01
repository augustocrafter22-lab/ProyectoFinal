<?php

/**
 * Controlador que procesa el ingreso de un nuevo ticket.
 *
 * Requiere sesión de docente activa y una solicitud POST con
 * "laboratorio", "equipo", "asunto", "descripcion", "turno", "grupo"
 * y "profesor". Registra el ticket y redirige con éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";
require_once RUTA_MODELO . "/Validador.php";

session_start();

if (!isset($_SESSION["cedula"]) || !($_SESSION["docente"] ?? false)) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como docente."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php");
    exit;
}

try {
    $laboratorio = Validador::longitud($_POST["laboratorio"] ?? "", 1, 20, "laboratorio");
    $equipo = Validador::longitud($_POST["equipo"] ?? "", 1, 10, "equipo");
    $asunto = Validador::longitud($_POST["asunto"] ?? "", 1, 100, "asunto");
    $descripcion = Validador::requerido($_POST["descripcion"] ?? "", "descripción");
    $turno = Validador::longitud($_POST["turno"] ?? "", 1, 15, "turno");
    $grupo = Validador::longitud($_POST["grupo"] ?? "", 1, 10, "grupo");
    $profesor = Validador::longitud($_POST["profesor"] ?? "", 1, 50, "profesor");

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

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
    exit;
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/IngresoDeTickets.php?error=" . urlencode($e->getMessage()));
    exit;
}

?>