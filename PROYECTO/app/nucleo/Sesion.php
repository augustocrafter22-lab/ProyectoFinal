<?php

require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Traductor.php";
require_once RUTA_MODELO . "/Usuario.php";

/**
 * Verifica acceso a la api con el token JWT
 */
class Sesion
{
    private static ?object $claims = null;

    private static function obtenerClaims():object{
        if (self::$claims === null){
            self::$claims = Token::verificar();
        }
        return self::$claims;
    }

    private static function Cedula():string{
        return (string) self::obtenerClaims()->sub;
    }

    public static function verificarRolApi(array $roles): void {
        $rolesUsuario = (array) self::obtenerClaims()->roles;
        foreach ($roles as $rol) {
            if (in_array($rol, $rolesUsuario, true)) {
                return;
            }
        }
        RespuestaJson::error(Traductor::t("comun.errorRolIncorrecto"), 403);
    }
    public static function verificarSesionIniciada():void{
        self::obtenerClaims();
    }
}