<?php

/**
 * Controlador que carga la vista de consulta de diagnósticos.
 *
 * Admite un filtro opcional por ticket vía GET ("ticket") y muestra
 * el listado de diagnósticos correspondiente en la vista.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDiagnostico.php";

// El filtro por ticket viaja por GET, como antes lo solia hacer ConsultarDiagnostico.js
// leyendo "?ticket=" desde la URL, solo que ahora se resuelve contra la BD.
$ticketFiltro = isset($_GET["ticket"]) ? trim($_GET["ticket"]) : "";

$conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
$conexion = $conectorPDO->establecerConexion();

$accesoDatosDiagnostico = new AccesoDatosDiagnostico($conexion);
$diagnosticos = $accesoDatosDiagnostico->listarDiagnosticos($ticketFiltro !== "" ? $ticketFiltro : null);

$conectorPDO->desconectar();

require_once RUTA_VISTA . "/consultarDiagnostico.php";

?>