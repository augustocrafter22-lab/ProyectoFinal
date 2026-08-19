<?php

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

$cedula = trim($_POST["ci"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$clave = trim($_POST["contrasenia"] ?? "");
$roles = $_POST["roles"] ?? [];

if (empty($cedula) || empty($clave) || empty($roles)) {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Todos los campos son requeridos"));
    exit;
}

if (strlen($cedula) !== 8 || !ctype_digit($cedula)) {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("CI debe tener 8 dígitos"));
    exit;
}

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);

    if ($altaDatosUsuario->usuarioExiste($cedula)) {
        header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("El usuario ya existe"));
        $conectorPDO->desconectar();
        exit;
    }

    $claveHasheada = password_hash($clave, PASSWORD_BCRYPT);
    $resultado = $altaDatosUsuario->crearUsuario($cedula, $nombre, $apellido, $claveHasheada, 1, $roles);

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/Administrador.php?exito=" . urlencode("Usuario creado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Error al crear el usuario"));
    }

} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Error: " . $e->getMessage()));
}
exit;