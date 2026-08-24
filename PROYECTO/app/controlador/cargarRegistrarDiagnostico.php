<?php

/**
 * Controlador que carga la vista para registrar un diagnóstico.
 *
 * Obtiene el listado de tickets disponibles para que el técnico
 * seleccione a cuál asociar el nuevo diagnóstico.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosTicket = new AccesoDatosTicket($conexion);
$tickets = $accesoDatosTicket->listarTickets();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/registrarDiagnostico.php";

?>