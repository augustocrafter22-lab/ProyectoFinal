<?php

/**
 * Endpoint de API para DIAGNOSTICO.
 *
 * Este archivo es el único punto de entrada de la API de diagnósticos,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorDiagnostico.php";

$controladorDiagnostico = new ControladorDiagnostico();
$controladorDiagnostico->gestionar();

?>
