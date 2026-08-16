<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/Login.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/app/vista/login.php");
    exit;
}

$cedula = trim($_POST["username"] ?? "");
$clave = $_POST["clave"] ?? "";

if (empty($cedula) || empty($clave)) {
    header("Location: " . URL_BASE . "/app/vista/login.php?error=" . urlencode("Ingrese cédula y contraseña"));
    exit;
}

try {
    $conectorPDO = new ConectorPDO(BD_HOST, BD_USER, BD_PASS, BD_NAME);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    $login = new Login($accesoDatosUsuario);
    $usuario = $login->autenticar($cedula, $clave);

    $conectorPDO->desconectar();

    if ($usuario === null) {
        header("Location: " . URL_BASE . "/app/vista/login.php?error=" . urlencode($login->getError()));
        exit;
    }

    if (!$usuario->tieneAlgunRol()) {
        header("Location: " . URL_BASE . "/app/vista/login.php?error=" . urlencode("Usuario sin roles habilitados"));
        exit;
    }

    session_start();
    session_regenerate_id(true);

    $_SESSION["cedula"] = $usuario->getCedula();
    $_SESSION["coordinador"] = $usuario->esCoordinador();
    $_SESSION["tecnico"] = $usuario->esTecnico();
    $_SESSION["roles"] = $usuario->getRoles();

    if ($usuario->esCoordinador() && $usuario->esTecnico()) {
        header("Location: " . URL_BASE . "/public/PanelRoles.php");
    } elseif ($usuario->esCoordinador()) {
        header("Location: " . URL_BASE . "/app/vista/administrador.php");
    } elseif ($usuario->esTecnico()) {
        header("Location: " . URL_BASE . "/public/Tecnico.html");
    } else {
        header("Location: " . URL_BASE . "/app/vista/login.php?error=" . urlencode("No tiene permisos"));
    }
    exit;

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/app/vista/login.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}
?>