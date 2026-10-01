<?php

/**
 * Controlador que carga la vista para registrar una reparación.
 *
 * Obtiene el listado de diagnósticos disponibles para que el técnico
 * seleccione a cuál asociar la nueva reparación.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosReparacion.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosReparacion = new AccesoDatosReparacion($conexion);
    $accesoDatosEquipo = new AccesoDatosEquipo($conexion);
    $diagnosticos = $accesoDatosReparacion->listarDiagnosticosDisponibles();

    $conectorPDO->desconectar();
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo "Ocurrió un error, intente nuevamente.";
    exit;
}

require_once RUTA_VISTA . "/registrarReparacion.php";

?>