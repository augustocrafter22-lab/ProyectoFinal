<?php

require_once __DIR__ . "/../modelo/ConectorPDO.php";
require_once __DIR__ . "/../modelo/Usuario.php";
require_once __DIR__ . "/../modelo/AccesoDatosUsuario.php";
require_once __DIR__ . "/../modelo/Login.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../HTML/Login.php");
    exit;
}

$cedula = trim($_POST["username"] ?? "");
$clave = $_POST["clave"] ?? "";

$conectorPDO = new ConectorPDO("localhost", "root", "", "sgrsi");
$conexion = $conectorPDO->establecerConexion();

$accesoDatosUsuario = new AccesoDatosUsuario($conexion);
$login = new Login($accesoDatosUsuario);

$conectorPDO->desconectar();

$usuario = $login->autenticar($cedula, $clave);

if ($usuario === null) {
    header("Location: ../HTML/Login.php?error=" . urlencode($login->getError()));
    exit;
}

if (!$usuario->tieneAlgunRol()) {
    header("Location: ../HTML/Login.php?error=" . urlencode("El usuario no tiene roles habilitados para ingresar."));
    exit;
}

session_start();
session_regenerate_id(true);

$_SESSION["cedula"] = $usuario->getCedula();
$_SESSION["coordinador"] = $usuario->esCoordinador();
$_SESSION["tecnico"] = $usuario->esTecnico();

if ($_SESSION["coordinador"] && $_SESSION["tecnico"]) {
    header("Location: ../HTML/PanelRoles.php");
} elseif ($_SESSION["coordinador"]) {
    header("Location: ../HTML/Coordinador.php");
} elseif ($_SESSION["tecnico"]) {
    header("Location: ../HTML/Tecnico.php");
}

exit;

?>