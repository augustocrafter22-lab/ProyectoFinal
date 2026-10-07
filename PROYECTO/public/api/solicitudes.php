<?php

/**
 * Endpoint de API para SOLICITUD_LABORATORIO.
 *
 * Este archivo es el único punto de entrada de la API de solicitudes de
 * laboratorio, carga la configuración del proyecto, crea el controlador
 * unificado y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorSolicitudLaboratorio.php";

session_start();

$controladorSolicitud = new ControladorSolicitudLaboratorio();
$controladorSolicitud->gestionar();

?>
