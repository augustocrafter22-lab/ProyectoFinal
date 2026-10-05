<?php

require_once RUTA_VISTA . "/RespuestaJson.php";

/**
 * Genera y verifica el token CSRF, para que solo las páginas del
 * sistema puedan hacer POST, PUT o DELETE en la API.
 */
class Token
{
    /**
     * Devuelve el token CSRF de la sesión, si no existe lo crea.
     *
     * @return string Token de la sesión.
     */
    public static function generarTokenCSRF(): string
    {
        if (!isset($_SESSION["csrfToken"])) {
            $_SESSION["csrfToken"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrfToken"];
    }

    /**
     * Verifica que el token de la cabecera X-CSRF-Token sea el de la sesión.
     *
     * @return void
     */
    public static function verificarCSRF(): void
    {
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (!isset($_SESSION["csrfToken"]) || !hash_equals($_SESSION["csrfToken"], $token)) {
            RespuestaJson::error("Solicitud rechazada.", 403);
        }
    }
}

?>
