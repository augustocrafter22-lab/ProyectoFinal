<?php

require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Token.php";

/**
 * Agrupa las verificaciones de sesión para que todas las páginas
 * protegidas controlen el acceso de la misma manera.
 */
class Sesion
{
    /**
     * Verifica que haya una sesión iniciada; si no, redirige al login.
     *
     * @return void
     */
    public static function verificarSesion(): void
    {
        if (!isset($_SESSION["cedula"])) {
            $mensaje = "Debe iniciar sesión para acceder a esa página.";
            header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode($mensaje));
            exit;
        }
    }

    /**
     * Verifica que haya una sesión iniciada y que el usuario tenga el rol
     * indicado; si no, redirige al login.
     *
     * @param string $rol Rol requerido (coordinador, tecnico o docente).
     * @return void
     */
    public static function verificarRol(string $rol): void
    {
        self::verificarSesion();

        if (!($_SESSION[$rol] ?? false)) {
            $mensaje = "No tiene autorización para acceder a ese panel.";
            header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode($mensaje));
            exit;
        }
    }

    /**
     * Igual que verificarRol pero alcanza con que el usuario tenga uno
     * de los roles indicados; si no tiene ninguno, redirige al login.
     *
     * @param array $roles Roles permitidos (coordinador, tecnico o docente).
     * @return void
     */
    public static function verificarAlgunRol(array $roles): void
    {
        self::verificarSesion();

        foreach ($roles as $rol) {
            if ($_SESSION[$rol] ?? false) {
                return;
            }
        }

        $mensaje = "No tiene autorización para acceder a ese panel.";
        header("Location: " . URL_BASE . "/public/paginas/Login.php?error=" . urlencode($mensaje));
        exit;
    }

    /**
     * Igual que verificarRol pero para la API, responde JSON 401 si no hay
     * sesión y 403 si el usuario no tiene ninguno de los roles.
     *
     * @param array $roles Roles permitidos (coordinador, tecnico o docente).
     * @return void
     */
    public static function verificarRolApi(array $roles): void
    {
        if (!isset($_SESSION["cedula"])) {
            RespuestaJson::error("Acceso denegado: sesión no iniciada.", 401);
        }

        foreach ($roles as $rol) {
            if ($_SESSION[$rol] ?? false) {
                return;
            }
        }

        RespuestaJson::error("Acceso denegado: rol incorrecto.", 403);
    }
}

?>
