<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDiagnostico.php";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosDiagnostico = new AccesoDatosDiagnostico($conexion);
$diagnosticos = $accesoDatosDiagnostico->listarDiagnosticos();

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/modificarDiagnostico.php";

?>