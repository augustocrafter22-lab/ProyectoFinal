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
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

$conectorPDO = null;

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
        throw new Exception("El usuario ya existe", 409);
    }

    // El hasheo se hace acá y el modelo recibe solo el hash
    $claveHasheada = password_hash($clave, PASSWORD_BCRYPT);

    // Mismo orden que los parámetros de crearUsuario: cedula, nombre, apellido, clave, activo, roles
    $resultado = $altaDatosUsuario->crearUsuario($cedula, $nombre, $apellido, $claveHasheada, 1, $roles);

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?exito=" . urlencode("Usuario creado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Error al crear el usuario"));
    }

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode($e->getMessage()));
} finally {
    // Se cierra la conexión pase lo que pase (éxito, error o usuario repetido)
    if ($conectorPDO !== null) {
        $conectorPDO->desconectar();
    }
}
exit;