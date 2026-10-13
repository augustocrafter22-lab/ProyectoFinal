<?php

/**
 * Controlador que procesa la activación de un usuario.
 *
 * Requiere una solicitud POST con "cedula". Activa al usuario indicado
 * y redirige al panel de administrador con un mensaje de éxito o error.
 */

require_once __DIR__ . "/../../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_NUCLEO . "/Token.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Método no permitido"));
    exit;
}

try {
    if (!Token::tokenValido($_POST["csrfToken"] ?? "")) {
        throw new Exception("Solicitud rechazada.", 403);
    }

    $cedula = Validador::cedula($_POST["cedula"] ?? "");

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);

    if (!$altaDatosUsuario->usuarioExiste($cedula)) {
        throw new Exception("No existe un usuario con la cédula $cedula.", 404);
    }

    $resultado = $altaDatosUsuario->activarUsuario($cedula);

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?exito=" . urlencode("Usuario Activado exitosamente"));
    } else {
        header("Location: " . URL_BASE . "/public/paginas/Administrador.php?error=" . urlencode("Error al activar el usuario"));
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