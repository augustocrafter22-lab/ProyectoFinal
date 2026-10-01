<?php

/**
 * Controlador que carga la vista del historial técnico por equipo.
 *
 * El listado de equipos y las reparaciones (filtradas por "?equipo=")
 * ahora se cargan con fetch contra la API (public/api/equipos.php y
 * public/api/reparaciones.php) desde HistorialTecnico.js.
 */

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/historialTecnico.php";

?>
