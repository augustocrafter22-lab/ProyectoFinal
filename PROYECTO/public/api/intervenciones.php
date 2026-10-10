<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorIntervencion.php";

session_start();

$controladorIntervencion = new ControladorIntervencion();
$controladorIntervencion->gestionar();

?>