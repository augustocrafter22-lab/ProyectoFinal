<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Acceso Denegado: Sesión no iniciada";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

if (!($_SESSION["coordinador"] ?? false)) {
    $mensaje = "Acceso Denegado: Rol incorrecto";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

//Comprueba que el formulario haya sido enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "Acceso Denegado: Petición incorrecta";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

require_once RUTA_CONTROLADOR . "/procesarEditarUsuario.php";