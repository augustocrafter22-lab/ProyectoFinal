<?php

header("Cache-Control: no-store, no-cache, must-revalidate");

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: Login.php?error=" . urlencode("Debe iniciar sesión para acceder a esa página."));
    exit;
}

if (!isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    header("Location: Login.php?error=" . urlencode("No tiene autorización para acceder a ese panel."));
    exit;
}

?>