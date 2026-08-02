<?php

require_once __DIR__ . '/../modelo/ConectorPDO.php';
require_once __DIR__ . '/../modelo/Usuario.php';
require_once __DIR__ . '/../modelo/AccesoDatosUsuario.php';
require_once __DIR__ . '/../modelo/Login.php';

// Comprueba que el formulario haya sido enviado mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../vista/login.php?error=peticion_incorrecta");
    exit;
}

// las credenciales provenientes del formulario.
$cedula = trim($_POST["cedula"] ?? "");
$clave = $_POST["clave"] ?? "";

// la conexión con la base de datos.
$conectorPDO = new ConectorPDO(
    "localhost",
    "deklan",
    "123",
    "deklan"
);

$conexion = $conectorPDO->establecerConexion();

// los objetos necesarios para buscar y autenticar al usuario.
$accesoDatosUsuario = new AccesoDatosUsuario($conexion);
$login = new Login($accesoDatosUsuario);

// autenticar al usuario.
$usuario = $login->autenticar($cedula, $clave);

// cierra la conexión después de realizar la consulta.
$conectorPDO->desconectar();

// si las credenciales no coinciden, vuelve al login.
if ($usuario === null) {
    header("Location: ../vista/login.php?error=credenciales_incorrectas");
    exit;
}

// comprueba si el usuario no tiene ningún rol habilitado.
if (
    !$usuario->esAdministrador() &&
    !$usuario->esTecnico() &&
    !$usuario->esDocente()
) {
    header("Location: ../vista/login.php?error=sin_roles");
    exit;
}

// inicia la sesión del usuario autenticado.
session_start();
session_regenerate_id(true);

// guarda la cédula y los roles en la sesión.
$_SESSION["cedula"] = $usuario->getCedula();
$_SESSION["administrador"] = $usuario->esAdministrador();
$_SESSION["tecnico"] = $usuario->esTecnico();
$_SESSION["docente"] = $usuario->esDocente();

// primero comprueba el caso del usuario con ambos roles.

if ($_SESSION["administrador"] && $_SESSION["tecnico"]) {
    header("Location: ../vista/administrador.php");
} elseif ($_SESSION["administrador"]) {
    header("Location: ../vista/administrador.php");
} elseif ($_SESSION["tecnico"]) {
    header("Location: ../vista/tecnico.php");
} elseif ($_SESSION["docente"]) {
    header("Location: ../vista/docente.php");
}

exit;
?>