<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorReemplazo.php";

session_start();

$controladorReemplazo = new ControladorReemplazo();
$controladorReemplazo->gestionar();

?>