<?php
require_once __DIR__ . "/config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/Login.php";

$conectorPDO = new ConectorPDO("localhost", "deklan", "123", "test");
$conexion = $conectorPDO->establecerConexion();

if ($conexion === null) {
    echo "No se conectó a la BD";
    exit;
}

echo "Conexión exitosa<br>";

$accesoDatosUsuario = new AccesoDatosUsuario($conexion);
$usuario = $accesoDatosUsuario->buscarUsuario("12345678");

if ($usuario === null) {
    echo "Usuario no encontrado";
    exit;
}

echo "Usuario encontrado: " . $usuario->getCedula() . "<br>";
echo "Clave: " . $usuario->getClaveHash() . "<br>";
echo "Roles: " . json_encode($usuario->getRoles()) . "<br>";
echo "Activo: " . ($usuario->estaActivo() ? "Sí" : "No") . "<br>";

$login = new Login($accesoDatosUsuario);
$resultado = $login->autenticar("12345678", "123456");

if ($resultado === null) {
    echo "Autenticación falló: " . $login->getError();
} else {
    echo "Autenticación exitosa";
}

$conectorPDO->desconectar();
?>