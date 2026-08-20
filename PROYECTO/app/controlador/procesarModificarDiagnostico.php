<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDiagnostico.php";

session_start();

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como técnico."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/ModificarDiagnostico.php");
    exit;
}

$idDiagnostico = trim($_POST["idDiagnostico"] ?? "");
$diagnostico = trim($_POST["diagnostico"] ?? "");

if ($idDiagnostico === "" || strlen($diagnostico) < 10) {
    $mensaje = "Debe seleccionar un diagnóstico e ingresar un texto de al menos 10 caracteres.";
    header("Location: " . URL_BASE . "/public/ModificarDiagnostico.php?error=" . urlencode($mensaje));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosDiagnostico = new AccesoDatosDiagnostico($conexion);
    $accesoDatosDiagnostico->actualizarDiagnostico((int) $idDiagnostico, $diagnostico);

    $conectorPDO->desconectar();

    header("Location: " . URL_BASE . "/public/ModificarDiagnostico.php?exito=" . urlencode("Diagnóstico modificado correctamente."));
    exit;

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/ModificarDiagnostico.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}

?>