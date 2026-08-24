<?php

/**
 * Controlador que carga la vista general de laboratorios.
 *
 * Obtiene los laboratorios y las solicitudes registradas y los pasa
 * a la vista correspondiente para su despliegue.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSolicitudLaboratorio.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosSolicitudLaboratorio = new AccesoDatosSolicitudLaboratorio($conexion);

    $laboratorios = $accesoDatosSolicitudLaboratorio->obtenerLaboratorios();
    $solicitudes = $accesoDatosSolicitudLaboratorio->obtenerSolicitudes();

    $conectorPDO->desconectar();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

require_once RUTA_VISTA . "/vistaLab.php";

?>