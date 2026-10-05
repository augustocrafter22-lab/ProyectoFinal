<?php

/**
 * Controlador que carga el panel de selección de roles.
 *
 * Busca al usuario autenticado (por su cédula en sesión) y lo redirige
 * al login si no existe; en caso contrario muestra la vista con sus roles.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

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
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode(Traductor::t("common.usuarioNoEncontrado")));
        exit;
    }

} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo Traductor::t("common.errorGenerico");
    exit;
}

require_once RUTA_VISTA . "/panelRoles.php";

?>