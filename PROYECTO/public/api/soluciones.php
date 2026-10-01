<?php

/**
 * Endpoint de API para SOLUCION.
 *
 * Este archivo es el único punto de entrada de la API de soluciones,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorSolucion.php";

$controladorSolucion = new ControladorSolucion();
$controladorSolucion->gestionar();

?>
