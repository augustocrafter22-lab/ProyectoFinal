<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosEquipo = new AccesoDatosEquipo($conexion);
$equipos = $accesoDatosEquipo->obtenerEquipos();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/ingresoTickets.php";

?>