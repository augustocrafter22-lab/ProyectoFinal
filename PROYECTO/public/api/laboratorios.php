<?php

/**
 * Endpoint de API para LABORATORIO.
 *
 * Este archivo es el único punto de entrada de la API de laboratorios,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorLaboratorio.php";

session_start();

$controladorLaboratorio = new ControladorLaboratorio();
$controladorLaboratorio->gestionar();

?>
