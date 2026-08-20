<?php


require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";


try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    
    $usuario = $accesoDatosUsuario->buscarUsuario($_SESSION["cedula"]);

    $conectorPDO->desconectar();

    if ($usuario === null) {
        header("Location: " . URL_BASE . "/public/login.php?error=" . urlencode("Usuario no encontrado"));
        exit;
}

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
    require_once RUTA_VISTA . "/docente.php";

    ?>