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
     * Indica si el token recibido es el de la sesión.
     *
     * @param mixed $token Token recibido del cliente (cabecera o campo de formulario).
     * @return bool true si coincide con el token de la sesión.
     */
    public static function tokenValido($token): bool
    {
        // Si el cliente manda un arreglo (csrfToken[]=...) se rechaza directamente.
        if (!isset($_SESSION["csrfToken"]) || is_array($token)) {
            return false;
        }

        return hash_equals($_SESSION["csrfToken"], $token);
    }

    /**
     * Verifica que el token de la cabecera X-CSRF-Token sea el de la sesión.
     *
     * @return void
     */
    public static function verificarCSRF(): void
    {
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (!self::tokenValido($token)) {
            RespuestaJson::error("Solicitud rechazada.", 403);
        }
    }
}

?>
