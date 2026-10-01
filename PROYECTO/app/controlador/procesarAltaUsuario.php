<?php

/**
 * Controlador que procesa el alta de un nuevo usuario.
 *
 * Requiere una solicitud POST con "ci", "nombre", "apellido", "contrasenia"
 * y "roles". Valida los datos, verifica que el usuario no exista, hashea
 * la contraseña y crea el usuario, redirigiendo con éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";
require_once RUTA_MODELO . "/Validador.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

try {
    $cedula = Validador::cedula($_POST["ci"] ?? "");
    $nombre = Validador::longitud($_POST["nombre"] ?? "", 1, 12, "nombre");
    $apellido = Validador::longitud($_POST["apellido"] ?? "", 1, 16, "apellido");
    $clave = Validador::requerido($_POST["contrasenia"] ?? "", "contraseña");
    $roles = $_POST["roles"] ?? [];

    if (empty($roles)) {
        throw new Exception("Debe seleccionar al menos un rol.", 400);
    }

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

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/Administrador.php?error=" . urlencode($e->getMessage()));
}
exit;