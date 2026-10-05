<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

require_once RUTA_NUCLEO . "/Sesion.php";
Sesion::verificarSesion();

// Verificar que tenga más de un rol (si no, no necesita seleccionar)
$cantidadRoles = (int) $_SESSION["coordinador"] + (int) $_SESSION["tecnico"] + (int) $_SESSION["docente"];

if ($cantidadRoles <= 1) {
    if ($_SESSION["coordinador"]) {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php");
    } elseif ($_SESSION["tecnico"]) {
        header("Location: " . URL_BASE . "/public/paginas/Tecnico.php");
    } elseif ($_SESSION["docente"]) {
        header("Location: " . URL_BASE . "/public/paginas/Docente.php");
    } else {
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode("No tiene permisos"));
    }
    exit;
}

require_once RUTA_CONTROLADOR . "/cargarPanelRol.php";

?>
