<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "acceso denegado: sesion no iniciada";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;
}

if (!($_SESSION["coordinador"] ?? false)) {
    $mensaje = "acceso denegado: faltan permisos";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;

}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "acceso denegado: metodo no permitido";
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode($mensaje));
    exit;
}


require_once RUTA_CONTROLADOR . "/procesarDesactivarUsuario.php";