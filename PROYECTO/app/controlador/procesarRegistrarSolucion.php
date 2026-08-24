<?php

/**
 * Controlador que procesa el registro de una solución.
 *
 * Requiere sesión de técnico activa y una solicitud POST con
 * "idDiagnostico" (numérico) y "solucion" (mínimo 10 caracteres).
 * El "modo" ("reparacion" u otro) determina a qué vista se redirige
 * tras el registro, con un mensaje de éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSolucion.php";

session_start();

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como técnico."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/RegistrarSolucion.php");
    exit;
}

$idDiagnostico = trim($_POST["idDiagnostico"] ?? "");
$solucion = trim($_POST["solucion"] ?? "");
$cedulaTecnico = $_SESSION["cedula"];
$esReparacion = ($_POST["modo"] ?? "") === "reparacion";
$paginaRegistro = $esReparacion ? "RegistrarReparacion.php" : "RegistrarSolucion.php";

if ($idDiagnostico === "" || !ctype_digit($idDiagnostico) || strlen($solucion) < 10) {
    $mensaje = "Debe seleccionar el diagnóstico e ingresar una solución de al menos 10 caracteres.";
    header("Location: " . URL_BASE . "/public/" . $paginaRegistro . "?error=" . urlencode($mensaje));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosSolucion = new AccesoDatosSolucion($conexion);
    $accesoDatosSolucion->registrarSolucion((int) $idDiagnostico, $cedulaTecnico, $solucion);

    $conectorPDO->desconectar();

    $mensajeExito = $esReparacion ? "Reparación registrada correctamente." : "Solución registrada correctamente.";
    header("Location: " . URL_BASE . "/public/" . $paginaRegistro . "?exito=" . urlencode($mensajeExito));
    exit;

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/" . $paginaRegistro . "?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}

?>