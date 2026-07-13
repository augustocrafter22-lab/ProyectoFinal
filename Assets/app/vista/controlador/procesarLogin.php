<?php

require_once __DIR__ . "/modelo/Usuario.php";
require_once __DIR__ . "/modelo/ConsultaUsuario.php";
require_once __DIR__ . "/modelo/Login.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Login.php");
    exit;
}

$cedula = trim($_POST["username"] ?? "");
$clave = $_POST["clave"] ?? "";

$consultaUsuario = new ConsultaUsuario();
$login = new Login($consultaUsuario);

$usuario = $login->autenticar($cedula, $clave);

if ($usuario === null) {
    header("Location: Login.php?error=1");
    exit;
}

session_start();
session_regenerate_id(true);

$_SESSION["cedula"] = $usuario->getCedula();
$_SESSION["rol"] = $usuario->getRol();

switch ($usuario->getRol()) {
    case "coordinador":
        header("Location: ../HTML/Coordinador.html");
        break;

    case "tecnico":
        header("Location: ../HTML/Tecnico.html");
        break;


    default:
        header("Location: Login.php?error=1");
}

exit;

?>