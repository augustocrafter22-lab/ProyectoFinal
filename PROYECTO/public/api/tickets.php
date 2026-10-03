<?php

/**
 * Endpoint de API para TICKET.
 *
 * Este archivo es el único punto de entrada de la API de tickets,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorTicket.php";

session_start();

$controladorTicket = new ControladorTicket();
$controladorTicket->gestionar();

?>