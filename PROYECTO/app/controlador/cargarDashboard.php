<?php

/**
 * Controlador que carga el dashboard de tickets.
 *
 * Lo comparten el coordinador y el técnico: obtiene las métricas de los
 * tickets y las pasa a la vista, junto con la página a la que vuelve cada rol.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDashboard.php";
require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

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
    RegistradorErrores::registrar($e);
    echo Traductor::t("common.errorGenerico");
    exit;
}

// El coordinador vuelve a su panel y el técnico al suyo.
$paginaInicio = $_SESSION["coordinador"] ? "Administrador.php" : "Tecnico.php";

require_once RUTA_VISTA . "/dashboard.php";

?>
