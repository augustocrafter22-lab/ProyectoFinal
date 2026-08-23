<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Debe iniciar sesión para acceder a esa página.";
    header("Location: Login.php?error=" . urlencode($mensaje));
    exit;
}

if (!isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    $mensaje = "No tiene autorización para acceder a ese panel.";
    header("Location: Login.php?error=" . urlencode($mensaje));
    exit;
}

$modoReparacion = true;
require_once RUTA_CONTROLADOR . "/cargarRegistrarSolucion.php";

?>
