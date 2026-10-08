<?php

/**
 * Endpoint de API para PRESTAMO.
 *
 * Este archivo es el único punto de entrada de la API de préstamos,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorPrestamo.php";

session_start();

$controladorPrestamo = new ControladorPrestamo();
$controladorPrestamo->gestionar();

?>
