<?php

/**
 * Controlador que carga el panel de administrador.
 *
 * Obtiene el listado completo de usuarios registrados y lo pasa
 * a la vista de administración para su despliegue.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    
    $usuarios = $accesoDatosUsuario->obtenerTodos();
    
    $conectorPDO->desconectar();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
    require_once RUTA_VISTA . "/administrador.php";

    ?>