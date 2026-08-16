<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Acceso Denegado: Sesión no iniciada";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}


if (!($_SESSION["administrador"] ?? false)) {
    $mensaje = "Acceso Denegado: Rol incorrecto";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

require_once RUTA_CONTROLADOR . "/procesarAltaUsuario.php";

?>