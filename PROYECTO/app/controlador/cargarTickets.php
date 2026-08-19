<?php

require_once __DIR__ . "/../modelo/ConectorPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosTicket.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $tickets = $accesoDatosTicket->listarTickets();

$conectorPDO->desconectar();

require_once __DIR__ . "/../vistaTickets.php";

?>