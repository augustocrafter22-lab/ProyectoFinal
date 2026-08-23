<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosReparacion.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosReparacion = new AccesoDatosReparacion($conexion);
$accesoDatosEquipo = new AccesoDatosEquipo($conexion);
$diagnosticos = $accesoDatosReparacion->listarDiagnosticosDisponibles();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/registrarReparacion.php";

?>