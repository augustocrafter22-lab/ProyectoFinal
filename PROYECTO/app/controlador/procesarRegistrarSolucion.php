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
require_once RUTA_MODELO . "/Validador.php";

session_start();

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como técnico."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/RegistrarSolucion.php");
    exit;
}

$esReparacion = ($_POST["modo"] ?? "") === "reparacion";
$paginaRegistro = $esReparacion ? "RegistrarReparacion.php" : "RegistrarSolucion.php";

try {
    $idDiagnostico = Validador::numerico($_POST["idDiagnostico"] ?? "", "diagnóstico");
    $solucion = Validador::longitud($_POST["solucion"] ?? "", 10, 2000, "solución");
    $cedulaTecnico = $_SESSION["cedula"];

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

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/" . $paginaRegistro . "?error=" . urlencode("Ocurrió un error, intente nuevamente."));
    exit;
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/" . $paginaRegistro . "?error=" . urlencode($e->getMessage()));
    exit;
}

?>