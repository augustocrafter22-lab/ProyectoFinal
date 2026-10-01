<?php

/**
 * Controlador que procesa el registro de un nuevo diagnóstico.
 *
 * Requiere sesión de técnico activa y una solicitud POST con "idTicket"
 * y "diagnostico" (mínimo 10 caracteres). El técnico se toma de la
 * sesión. Redirige a la vista de registro con éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDiagnostico.php";
require_once RUTA_MODELO . "/Validador.php";

session_start();

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("Debe iniciar sesión como técnico."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/RegistrarDiagnostico.php");
    exit;
}

try {
    $idTicket = Validador::requerido($_POST["idTicket"] ?? "", "ticket");
    $diagnostico = Validador::longitud($_POST["diagnostico"] ?? "", 10, 2000, "diagnóstico");
    $cedulaTecnico = $_SESSION["cedula"];

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosDiagnostico = new AccesoDatosDiagnostico($conexion);
    $accesoDatosDiagnostico->registrarDiagnostico($idTicket, $cedulaTecnico, $diagnostico);

    $conectorPDO->desconectar();

    header("Location: " . URL_BASE . "/public/RegistrarDiagnostico.php?exito=" . urlencode("Diagnóstico registrado correctamente."));
    exit;

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/RegistrarDiagnostico.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
    exit;
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/RegistrarDiagnostico.php?error=" . urlencode($e->getMessage()));
    exit;
}

?>