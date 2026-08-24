<?php

/**
 * Controlador que carga el panel de técnico.
 *
 * Busca al usuario autenticado (por su cédula en sesión), y si existe
 * carga las métricas del dashboard y las reparaciones/soluciones
 * registradas para mostrarlas en la vista de técnico.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/AccesoDatosDashboard.php";
require_once RUTA_MODELO . "/AccesoDatosSolucion.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    $usuario = $accesoDatosUsuario->buscarUsuario($_SESSION["cedula"]);

    if ($usuario === null) {
        $conectorPDO->desconectar();
        header("Location: " . URL_BASE . "/public/login.php?error=" . urlencode("Usuario no encontrado"));
        exit;
    }

    $accesoDatosDashboard = new AccesoDatosDashboard($conexion);
    $accesoDatosSolucion = new AccesoDatosSolucion($conexion);

    $totalReportes = $accesoDatosDashboard->contarTotal();
    $porEstado = $accesoDatosDashboard->contarPorEstado();
    $tiemposResolucion = $accesoDatosDashboard->obtenerTiemposResolucion();
    $incidenciasPorSalon = $accesoDatosDashboard->obtenerIncidenciasPorSalon();
    $reparaciones = $accesoDatosSolucion->listarSolucionesConEquipo();

    $conectorPDO->desconectar();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

require_once RUTA_VISTA . "/tecnico.php";

?>