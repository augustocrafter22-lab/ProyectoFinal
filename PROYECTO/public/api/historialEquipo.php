<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorHistorialEquipo.php";

session_start();

$controladorHistorialEquipo = new ControladorHistorialEquipo();
$controladorHistorialEquipo->gestionar();

?>