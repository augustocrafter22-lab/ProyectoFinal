<?php

/**
 * Controlador que carga el historial de reparaciones por equipo.
 *
 * Admite un filtro opcional por equipo vía GET ("equipo") y muestra
 * el listado de equipos junto con las reparaciones correspondientes.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";
require_once RUTA_MODELO . "/AccesoDatosReparacion.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $idEquipo = trim($_GET["equipo"] ?? "");
    $accesoDatosEquipo = new AccesoDatosEquipo($conexion);
    $accesoDatosReparacion = new AccesoDatosReparacion($conexion);
    $equipos = $accesoDatosEquipo->obtenerEquipos();
    $reparaciones = $accesoDatosReparacion->listarReparaciones($idEquipo);

    $conectorPDO->desconectar();
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo "Ocurrió un error, intente nuevamente.";
    exit;
}

require_once RUTA_VISTA . "/historialTecnico.php";

?>

