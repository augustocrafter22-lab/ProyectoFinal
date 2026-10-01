<?php

/**
 * Controlador que carga la vista de gestión de equipos.
 *
 * El listado de equipos y el alta/modificación/baja ahora se manejan
 * con fetch contra la API (public/api/equipos.php) desde gestionPCs.js.
 * Acá solo se carga el listado de laboratorios.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";
require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosEquipo = new AccesoDatosEquipo($conexion);
    $laboratorios = $accesoDatosEquipo->obtenerLaboratorios();

    $conectorPDO->desconectar();
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo Traductor::t("common.errorGenerico");
    exit;
}

require_once RUTA_VISTA . "/equipos.php";
?>