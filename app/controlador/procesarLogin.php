<?php

require_once '../modelo/ConectorPDO.php';
require_once '../modelo/Usuario.php';
require_once '../modelo/ConsultaUsuario.php';
require_once '../modelo/Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: login.php");
    exit();
}

$cedula = trim($_POST['cedula'] ?? '');
$contraseña = $_POST['contraseña'] ?? '';

$consultaUsuario = new ConsultaUsuario();
$login = new Login();
$usuario = $login->autenticar($cedula, $contraseña);

if ($usuario === null) {
    exit('Cédula o contraseña incorrecta, o usuario inactivo.');
}

if ($usuario->esAdministrador()) {
    header("Location: ../vista/administrador.php");
    exit();
} elseif ($usuario->esTecnico()) {
    header("Location: ../vista/tecnico.php");
    exit();
} elseif ($usuario->esCoordinador()) {
    header("Location: ../vista/coordinador.php");
    exit();
} else {
    exit('Rol de usuario no reconocido.');
}

session_start();
session_regenerate_id(true);
$_SESSION['cedula'] = $usuario->getCedula();
$_SESSION['administrador'] = $usuario->esAdministrador() ? 'administrador' : '';
$_SESSION['tecnico'] = $usuario->esTecnico() ? 'tecnico' : '';
$_SESSION['coordinador'] = $usuario->esCoordinador() ? 'coordinador' : '';

header("Location: administrador.php");
exit();

?>
