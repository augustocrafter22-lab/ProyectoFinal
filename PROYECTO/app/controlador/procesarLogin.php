<?php

/**
 * Controlador que procesa el inicio de sesión.
 *
 * Requiere una solicitud POST con "username" (cédula) y "clave".
 * Autentica al usuario, inicia sesión con sus roles y lo redirige
 * al panel correspondiente según los roles que tenga habilitados.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/Login.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/Traductor.php";
require_once RUTA_NUCLEO . "/Token.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/paginas/Login.php");
    exit;
}

session_start();
Traductor::iniciar();

try {
    $cedula = Validador::requerido($_POST["username"] ?? "", "cédula");
    $clave = Validador::requerido($_POST["clave"] ?? "", "contraseña");

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    $login = new Login($accesoDatosUsuario);
    $usuario = $login->autenticar($cedula, $clave);

    $conectorPDO->desconectar();

    if ($usuario === null) {
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode($login->getError()));
        exit;
    }

    if (!$usuario->tieneAlgunRol()) {
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode(Traductor::t("login.sinRoles")));
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["cedula"] = $usuario->getCedula();
    $_SESSION["coordinador"] = $usuario->esCoordinador();
    $_SESSION["tecnico"] = $usuario->esTecnico();
    $_SESSION["docente"] = $usuario->esDocente();
    $_SESSION["roles"] = $usuario->getRoles();

    // Se genera un token nuevo en cada inicio de sesión.
    unset($_SESSION["csrfToken"]);
    Token::generarTokenCSRF();

    $cantidadRoles = (int) $usuario->esCoordinador() + (int) $usuario->esTecnico() + (int) $usuario->esDocente();

    if ($cantidadRoles > 1) {
        header("Location: " . URL_BASE . "/public/paginas/PanelRoles.php");
    } elseif ($usuario->esCoordinador()) {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php");
    } elseif ($usuario->esTecnico()) {
        header("Location: " . URL_BASE . "/public/paginas/Tecnico.php");
    } elseif ($usuario->esDocente()) {
        header("Location: " . URL_BASE . "/public/paginas/Docente.php");
    } else {
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode(Traductor::t("login.sinPermisos")));
    }
    exit;

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode(Traductor::t("common.errorGenerico")));
    exit;
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>