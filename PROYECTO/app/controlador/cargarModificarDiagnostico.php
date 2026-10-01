<?php

/**
 * Controlador que carga la vista para modificar diagnósticos.
 *
 * Obtiene el listado completo de diagnósticos y lo pasa a la vista
 * correspondiente para que el técnico seleccione cuál modificar.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosDiagnostico.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosDiagnostico = new AccesoDatosDiagnostico($conexion);
    $diagnosticos = $accesoDatosDiagnostico->listarDiagnosticos();

    $conectorPDO->desconectar();
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo "Ocurrió un error, intente nuevamente.";
    exit;
}

require_once RUTA_VISTA . "/modificarDiagnostico.php";

?>