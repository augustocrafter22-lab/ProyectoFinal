<?php

/**
 * Entrega los textos del sistema en el idioma elegido por el usuario.
 *
 * Los textos están separados por secciones (un archivo por pantalla):
 * app/i18n/es/equipos.php, app/i18n/en/equipos.php, etc. Una sección
 * se lee del disco solo cuando alguien la pide, así cada pantalla carga
 * únicamente lo que necesita.
 */
class Traductor
{
    private const IDIOMAS_VALIDOS = ["es", "en"];

    private static string $idioma = "es";

    /** Secciones ya leídas, con la forma [seccion => [clave => texto]]. */
    private static array $secciones = [];

    /**
     * Toma el idioma guardado en la sesión (español por defecto).
     * Hay que llamarlo después de session_start().
     *
     * @return void
     */
    public static function iniciar(): void
    {
        $idioma = $_SERVER["HTTP_X_IDIOMA"] ?? "es";

        self::$idioma = self::esIdiomaValido($idioma) ? $idioma : "es";
        self::$secciones = [];
    }

    /**
     * @param mixed $idioma Código recibido del cliente.
     * @return bool true si es es o en.
     */
    public static function esIdiomaValido($idioma): bool
    {
        return in_array($idioma, self::IDIOMAS_VALIDOS, true);
    }

    /**
     * @return string Idioma en uso (es o en).
     */
    public static function obtenerIdioma(): string
    {
        return self::$idioma;
    }

    /**
     * Devuelve un texto a partir de su clave "seccion.clave".
     *
     * Los valores reemplazan a las {marcas} del texto, igual que traducir()
     * en el JS: t("equipos.registrado", ["id" => "PC-01"]).
     *
     * @param string $clave Por ejemplo "equipos.titulo".
     * @param array $valores Valores de las marcas, con la forma [marca => valor].
     * @return string El texto traducido o la misma clave si no existe.
     */
    public static function t(string $clave, array $valores = []): string
    {
        [$seccion, $nombre] = array_pad(explode(".", $clave, 2), 2, "");

        $texto = self::obtenerSeccion($seccion)[$nombre] ?? $clave;

        // Cambia cada {marca} por su valor, por ejemplo {id} por PC-01.
        foreach ($valores as $marca => $valor) {
            $texto = str_replace("{" . $marca . "}", (string) $valor, $texto);
        }

        return $texto;
    }

    /**
     * Devuelve varias secciones juntas, pensado para que el cliente las pida
     * en una sola solicitud. Las secciones que no existen se ignoran.
     *
     * @param array $nombres Nombres de las secciones pedidas.
     * @return array Arreglo con la forma [seccion => [clave => texto]].
     */
    public static function obtenerSecciones(array $nombres): array
    {
        $resultado = [];

        foreach ($nombres as $nombre) {
            $textos = self::obtenerSeccion((string) $nombre);

            if ($textos !== []) {
                $resultado[$nombre] = $textos;
            }
        }

        return $resultado;
    }

    /**
     * Lee una sección del disco la primera vez que se pide.
     *
     * @param string $seccion Nombre de la sección, solo letras (equipos, comun, etc.).
     * @return array Textos de la sección, vacío si no existe.
     */
    private static function obtenerSeccion(string $seccion): array
    {
        if (!isset(self::$secciones[$seccion])) {
            self::$secciones[$seccion] = self::leerSeccion($seccion);
        }

        return self::$secciones[$seccion];
    }

    /**
     * @param string $seccion Nombre de la sección.
     * @return array Contenido del archivo, vacío si el nombre es inválido o no existe.
     */
    private static function leerSeccion(string $seccion): array
    {
        // Solo letras: evita que el cliente pida rutas como ../../config.
        if (!preg_match("/^[A-Za-z]+$/", $seccion)) {
            return [];
        }

        $archivo = RUTA_APP . "/i18n/" . self::$idioma . "/" . $seccion . ".php";

        return is_file($archivo) ? require $archivo : [];
    }
}
