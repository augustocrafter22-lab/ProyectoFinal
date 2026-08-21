<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosTicket = new AccesoDatosTicket($conexion);
$tickets = $accesoDatosTicket->listarTickets();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/vistaTickets.php";

?>