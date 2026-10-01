<?php

/**
 * Endpoint de API para REPARACION.
 *
 * Este archivo es el único punto de entrada de la API de reparaciones,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar(). También sirve la
 * consulta del historial técnico de un equipo vía GET ?idEquipo=.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorReparacion.php";

$controladorReparacion = new ControladorReparacion();
$controladorReparacion->gestionar();

?>
