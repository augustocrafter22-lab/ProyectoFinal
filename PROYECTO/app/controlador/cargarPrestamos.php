<?php

/**
 * Controlador que carga la vista de préstamos y devoluciones de equipos.
 *
 * El listado de préstamos, el registro y la devolución se manejan con
 * fetch contra la API (public/api/prestamos.php) desde gestionPrestamos.js,
 * y los equipos disponibles se piden a public/api/equipos.php. Acá solo
 * se inicia el traductor y se carga la vista.
 */

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/prestamos.php";
?>
