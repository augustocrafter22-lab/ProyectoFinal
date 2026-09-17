<?php

/**
 * Endpoint de API para EQUIPO.
 *
 * Este archivo es el único punto de entrada de la API de equipos,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorEquipo.php";

$controladorEquipo = new ControladorEquipo();
$controladorEquipo->gestionar();

?>