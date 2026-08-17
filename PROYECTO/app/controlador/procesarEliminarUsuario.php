<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/app/vista/administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

$cedula = trim($_POST["cedula"] ?? "");

if (empty($cedula)) {
    header("Location: " . URL_BASE . "/app/vista/administrador.php?error=" . urlencode("CI requerido"));
    exit;
}

try {
    $conectorPDO = new ConectorPDO(BD_HOST, BD_USER, BD_PASS, BD_NAME);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);
    $resultado = $altaDatosUsuario->eliminarUsuario($cedula);

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/app/vista/administrador.php?exito=" . urlencode("Usuario eliminado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/app/vista/administrador.php?error=" . urlencode("Error al eliminar el usuario"));
    }

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/app/vista/administrador.php?error=" . urlencode("Error: " . $e->getMessage()));
}
exit;
?>