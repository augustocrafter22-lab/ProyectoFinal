<?php
 
/**
 * Endpoint de API para USUARIO.
 *
 * Este archivo es el único punto de entrada de la API de usuarios,
 * carga la configuración del proyecto, crea el controlador unificado
 * y le entrega la petición mediante gestionar().
 */
 
require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ControladorUsuario.php";
 
session_start();
 
$controladorUsuario = new ControladorUsuario();
$controladorUsuario->gestionar();
 
?>