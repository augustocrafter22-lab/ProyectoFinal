<?php

/**
 * Controlador que procesa el registro de una reparación.
 *
 * Requiere sesión de técnico activa y una solicitud POST con
 * "idDiagnostico" (numérico) y "reparacion" (mínimo 10 caracteres).
 * El técnico se toma de la sesión. Redirige con éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosReparacion.php";

session_start();

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como técnico."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/RegistrarReparacion.php");
    exit;
}

$idDiagnostico = trim($_POST["idDiagnostico"] ?? "");
$reparacion = trim($_POST["reparacion"] ?? "");
$cedulaTecnico = $_SESSION["cedula"];

if ($idDiagnostico === "" || !ctype_digit($idDiagnostico) || strlen($reparacion) < 10) {
    $mensaje = "Debe seleccionar un diagnóstico e ingresar una reparación de al menos 10 caracteres.";
    header("Location: " . URL_BASE . "/public/RegistrarReparacion.php?error=" . urlencode($mensaje));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosReparacion = new AccesoDatosReparacion($conexion);
    if (!$accesoDatosReparacion->registrarReparacion((int) $idDiagnostico, $cedulaTecnico, $reparacion)) {
        throw new Exception("El diagnóstico seleccionado no existe o no tiene un equipo asociado.");
    }

    $conectorPDO->desconectar();

    header("Location: " . URL_BASE . "/public/RegistrarReparacion.php?exito=" . urlencode("Reparación registrada correctamente."));
    exit;
} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/RegistrarReparacion.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}

?>