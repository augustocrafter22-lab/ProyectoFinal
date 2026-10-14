<?php

require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador del idioma del sistema.
 *
 *   GET  /public/index.php?ruta=traducciones&secciones=comun,equipos   textos de las secciones pedidas
 *   POST /public/index.php?ruta=idioma                                 cambia el idioma de la sesión
 *
 * No pide rol porque el login también necesita sus textos y el cambio de idioma.
 */
class ControladorIdioma
{
    /**
     * Devuelve solo las secciones de texto que la pantalla pide.
     *
     * @return void
     * @throws Exception Si el método no es GET o no se indica ninguna sección.
     */
    public function obtenerTraducciones(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }

        $secciones = array_filter(explode(",", (string) ($_GET["secciones"] ?? "")));

        if ($secciones === []) {
            throw new Exception(Traductor::t("comun.errorSeccionesFaltantes"), 400);
        }

        RespuestaJson::exito([
            "idioma" => Traductor::obtenerIdioma(),
            "secciones" => Traductor::obtenerSecciones($secciones)
        ], Traductor::t("comun.traduccionesCargadas"));
    }

    /**
     * Guarda en la sesión el idioma elegido (es o en).
     *
     * @return void
     * @throws Exception Si el método no es POST o el idioma no existe.
     */
    public function cambiarIdioma(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }

        $idioma = Solicitud::obtenerDatos()["idioma"] ?? "";

        if (!Traductor::esIdiomaValido($idioma)) {
            throw new Exception(Traductor::t("comun.errorIdiomaInvalido"), 400);
        }

        $_SESSION["idioma"] = $idioma;

        RespuestaJson::exito(["idioma" => $idioma], Traductor::t("comun.idiomaCambiado"));
    }
}
