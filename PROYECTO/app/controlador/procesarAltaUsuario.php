<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";


session_start();


//Comprueba que el formulario haya sido enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "Acceso Denegado: Petición incorrecta";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}


//Recupera los datos provenientes del formulario
$cedula = trim($_POST["cedula"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");

$clave = $_POST["clave"] ?? "";
$confirmarClave = $_POST["confirmarClave"] ?? "";

$cargo = trim($_POST["cargo"] ?? "");

if ($cedula === "" || $nombre === "" || $apellido === "" || $clave === "" || $confirmarClave === "" || $cargo === "" ) {
    $mensaje = "No se pudo registrar el empleado: existen campos vacíos.";
    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
    $mensaje = "No se pudo registrar el empleado: cédula incorrecta.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if (strlen($clave) < 12) {
    $mensaje = "La contraseña debe contener al menos 12 caracteres.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if ($clave !== $confirmarClave) {
    $mensaje = "Las contraseñas ingresadas no coinciden.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

$claveHash = password_hash($clave, PASSWORD_DEFAULT);

$conectorPDO = new ConectorPDO("localhost:3306", "leandro", "123", "test");

$conexion = $conectorPDO->establecerConexion();

if ($conexion === null) {
    $mensaje = "No se pudo establecer conexión con la base de datos.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

$altaDatosUsuario = new AltaDatosUsuario($conexion);

$resultado = $altaDatosUsuario->registrarUsuario($cedula, $nombre, $apellido, $claveHash, $cargo);

$conectorPDO->desconectar();


if (!$resultado) {
    $mensaje = "No se pudo registrar el empleado.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

$mensaje = "Usuario ingresado exitosamente.";
header("Location: administrador.php?resultado=" . urlencode($mensaje));
exit;

?>