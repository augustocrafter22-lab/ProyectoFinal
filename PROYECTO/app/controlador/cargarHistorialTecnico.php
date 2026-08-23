<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSolucion.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosSolucion = new AccesoDatosSolucion($conexion);
$reparaciones = $accesoDatosSolucion->listarSolucionesConEquipo();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/historialTecnico.php";

?>
