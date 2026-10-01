<?php

/**
 * Controlador que carga la vista de consulta de diagnósticos.
 *
 * El listado (y el filtro opcional por ticket, vía "?ticket=") ahora se
 * maneja con fetch contra la API (public/api/diagnosticos.php) desde
 * consultarDiagnostico.js.
 */

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/consultarDiagnostico.php";

?>
