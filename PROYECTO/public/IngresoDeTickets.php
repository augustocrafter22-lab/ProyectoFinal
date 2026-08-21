<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Debe iniciar sesión para acceder a esa página.";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;
}

if (!($_SESSION["docente"] ?? false)) {
    $mensaje = "No tiene autorización para acceder a ese panel.";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;
}

require_once RUTA_CONTROLADOR . "/cargarIngresoTickets.php";

?>