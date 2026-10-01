<?php

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
            header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
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
            header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
            exit;
        }
    }
}

?>
