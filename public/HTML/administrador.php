<?php

session_start();

if (isset($_SESSION['cedula'])) {
    header("Location: login.php");
    exit();
}
if ( !isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../app/vista/administrador.php";

?>