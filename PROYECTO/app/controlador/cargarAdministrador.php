<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";

try {
    $conectorPDO = new ConectorPDO(BD_HOST, BD_USER, BD_PASS, BD_NAME);
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
?>
<?php
    require_once __DIR__ . "/../vista/administrador.php";

    ?>