<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

$cedula = trim($_POST["cedula"] ?? "");

if (empty($cedula)) {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("CI requerido"));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);
    $resultado = $altaDatosUsuario->desactivarUsuario($cedula);

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/Administrador.php?exito=" . urlencode("Usuario desactivado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Error al desactivar el usuario"));
    }

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Error: " . $e->getMessage()));
}
exit;
?>