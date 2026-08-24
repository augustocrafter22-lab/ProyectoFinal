<?php

/**
 * Controlador que carga el dashboard de reportes.
 *
 * Obtiene el total de reportes, su distribución por estado, los
 * tiempos de resolución y las incidencias por salón, y los pasa
 * a la vista del dashboard.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDashboard.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosDashboard = new AccesoDatosDashboard($conexion);

    $totalReportes = $accesoDatosDashboard->contarTotal();
    $porEstado = $accesoDatosDashboard->contarPorEstado();
    $tiemposResolucion = $accesoDatosDashboard->obtenerTiemposResolucion();
    $incidenciasPorSalon = $accesoDatosDashboard->obtenerIncidenciasPorSalon();

    $conectorPDO->desconectar();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

require_once RUTA_VISTA . "/Dashboard.php";

?>