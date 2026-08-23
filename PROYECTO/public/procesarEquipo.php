<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"]) || !($_SESSION["tecnico"] ?? false)) {
    $mensaje = "No tiene autorización para gestionar equipos.";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;
}

require_once RUTA_CONTROLADOR . "/procesarEquipo.php";
?>
