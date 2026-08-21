<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: " . URL_BASE . "/public/Login.php");
    exit;
}

if (!isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    $mensaje = "No tiene autorización para acceder a ese panel.";
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
    exit;
}

require_once RUTA_CONTROLADOR . "/cargarVistaLab.php";

?>