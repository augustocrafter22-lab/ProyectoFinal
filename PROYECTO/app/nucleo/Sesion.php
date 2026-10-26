<?php

require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Traductor.php";
require_once RUTA_MODELO . "/Usuario.php";

/**
 * Agrupa todo lo relacionado con la sesión del usuario: iniciarla, cerrarla
 * y verificar el acceso, para que todos los endpoints controlen el acceso
 * de la misma manera.
 */
class Sesion
{
    /**
     * Guarda en la sesión los datos del usuario que acaba de autenticarse
     * y genera un token CSRF nuevo.
     *
     * @param Usuario $usuario Usuario autenticado.
     * @return void
     */
    public static function iniciar(Usuario $usuario): void
    {
        // Se cambia el identificador de sesión para evitar que se reutilice uno anterior.
        session_regenerate_id(true);

        $_SESSION["cedula"] = $usuario->getCedula();
        self::actualizarRoles($usuario);

        unset($_SESSION["csrfToken"]);
        Token::generarTokenCSRF();
    }

    /**
     * Guarda en la sesión los roles que el usuario tiene en la base de datos.
     * Se usa al iniciar sesión y cada vez que una pantalla consulta la sesión,
     * así un cambio de roles hecho por el coordinador se aplica sin volver a loguearse.
     *
     * @param Usuario $usuario Usuario con sus roles actuales.
     * @return void
     */
    public static function actualizarRoles(Usuario $usuario): void
    {
        $_SESSION["coordinador"] = $usuario->esCoordinador();
        $_SESSION["tecnico"] = $usuario->esTecnico();
        $_SESSION["docente"] = $usuario->esDocente();
        $_SESSION["roles"] = $usuario->getRoles();
    }

    /**
     * Cierra la sesión del usuario: borra sus datos y cambia el identificador
     * de sesión, así el anterior deja de servir. El idioma elegido no es un
     * dato del usuario, por eso se conserva.
     *
     * @return void
     */
    public static function cerrar(): void
    {
        $idioma = $_SESSION["idioma"] ?? "es";

        $_SESSION = ["idioma" => $idioma];
        session_regenerate_id(true);
    }

    /**
     * Verifica que haya una sesión iniciada y que el usuario tenga alguno
     * de los roles indicados; si no, responde JSON 401 o 403.
     *
     * @param array $roles Roles permitidos (coordinador, tecnico o docente).
     * @return void
     */
    public static function verificarRolApi(array $roles): void
    {
        self::verificarSesionIniciada();

        foreach ($roles as $rol) {
            if ($_SESSION[$rol] ?? false) {
                return;
            }
        }

        RespuestaJson::error(Traductor::t("comun.errorRolIncorrecto"), 403);
    }

    /**
     * Verifica que haya una sesión iniciada, sin importar el rol.
     *
     * @return void
     */
    public static function verificarSesionIniciada(): void
    {
        if (!isset($_SESSION["cedula"])) {
            RespuestaJson::error(Traductor::t("comun.errorSesionNoIniciada"), 401);
        }
    }
}
