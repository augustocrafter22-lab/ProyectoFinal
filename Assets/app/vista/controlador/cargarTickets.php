<?php

require_once __DIR__ . "/../modelo/ConectorPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosTicket.php";

$conectorPDO = new ConectorPDO("localhost", "root", "", "sgrsi");
$conexion = $conectorPDO->establecerConexion();

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $tickets = $accesoDatosTicket->listarTickets();

$conectorPDO->desconectar();

require_once __DIR__ . "/../vistaTickets.php";

?>