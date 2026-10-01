<?php

class Traductor
{
    private static array $textos = [];

    public static function iniciar(): void
    {
        $idioma = $_SESSION["idioma"] ?? "es";

        if ($idioma !== "es" && $idioma !== "en") {
            $idioma = "es";
        }

        self::$textos = require RUTA_APP . "/i18n/" . $idioma . ".php";
    }

    public static function t(string $clave): string
    {
        return self::$textos[$clave] ?? $clave;
    }
}

?>
