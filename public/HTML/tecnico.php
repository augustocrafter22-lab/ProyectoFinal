<?php

session_start();

if (isset($_SESSION['cedula'])) {
    header("Location: login.php");
    exit();
}
if ( !isset($_SESSION['tecnico']) || $_SESSION['tecnico'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../app/vista/tecnico.php";

?>