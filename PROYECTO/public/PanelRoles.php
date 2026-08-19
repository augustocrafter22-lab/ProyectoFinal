<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: " . URL_BASE . "/public/login.php");
    exit;
}

// Verificar que tenga más de un rol (si no, no necesita seleccionar)
$cantidadRoles = (int) $_SESSION["coordinador"] + (int) $_SESSION["tecnico"] + (int) $_SESSION["docente"];

if ($cantidadRoles <= 1) {
    if ($_SESSION["coordinador"]) {
        header("Location: " . URL_BASE . "/public/Administrador.php");
    } elseif ($_SESSION["tecnico"]) {
        header("Location: " . URL_BASE . "/public/Tecnico.html");
    } elseif ($_SESSION["docente"]) {
        header("Location: " . URL_BASE . "/public/Docente.html");
    } else {
        header("Location: " . URL_BASE . "/public/login.php?error=" . urlencode("No tiene permisos"));
    }
    exit;
}



require_once RUTA_CONTROLADOR . "/cargarPanelRol.php";

?>
