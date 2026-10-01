<?php

/**
 * Controlador que carga la vista general de tickets.
 *
 * El listado de tickets ahora se carga con fetch contra la API
 * (public/api/tickets.php) desde vistaTickets.js, así que acá no
 * hace falta consultar la base de datos.
 */

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/vistaTickets.php";

?>
