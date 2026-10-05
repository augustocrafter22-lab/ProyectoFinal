<?php

/**
 * Controlador que procesa la edición de un usuario existente.
 *
 * Requiere una solicitud POST con "ci" y "roles" (nombre, apellido y
 * contrasenia son opcionales; se actualizan solo si vienen no vacíos).
 * Redirige al panel de administrador con un mensaje de éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";
require_once RUTA_MODELO . "/Validador.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

try {
    $cedula = Validador::cedula($_POST["ci"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $clave = trim($_POST["contrasenia"] ?? "");
    $roles = $_POST["roles"] ?? [];

    if ($nombre !== "") {
        $nombre = Validador::longitud($nombre, 1, 12, "nombre");
    }
    if ($apellido !== "") {
        $apellido = Validador::longitud($apellido, 1, 16, "apellido");
    }
    if (empty($roles)) {
        throw new Exception("Debe seleccionar al menos un rol.", 400);
    }

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);

    $resultado = $altaDatosUsuario->actualizarUsuario(
        $cedula,
        !empty($nombre) ? $nombre : null,
        !empty($apellido) ? $apellido : null,
        !empty($clave) ? $clave : null,
        $roles,
        null // activo no se toca desde este formulario
    );

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?exito=" . urlencode("Usuario actualizado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Error al actualizar el usuario"));
    }

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode($e->getMessage()));
}
exit;
?>