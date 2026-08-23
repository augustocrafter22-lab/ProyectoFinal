<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: " . URL_BASE . "/public/Login.php");
    exit;
}

if (!isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode("No tiene autorización para acceder a ese panel."));
    exit;
}

require_once RUTA_CONTROLADOR . "/cargarHistorialTecnico.php";

?>