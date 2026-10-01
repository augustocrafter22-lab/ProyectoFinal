<?php

/**
 * Controlador que carga la vista para registrar un diagnóstico.
 *
 * El listado de tickets ahora se carga con fetch contra la API
 * (public/api/tickets.php) desde registrarDiagnostico.js.
 */

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/registrarDiagnostico.php";

?>
