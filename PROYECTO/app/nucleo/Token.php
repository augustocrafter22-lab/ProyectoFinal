<?php

require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Traductor.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Genera e identifica a los usuarios con el token JWT
 */

class Token
{ 
    private const DuracionToken = 3600; // 1 hora


    /**
     * Genera un token JWT para un usuario 
     * @param usuario $u Usuario que se quiere identificar
     * @return string Token JWT generado
     */
    public static function generar(usuario $u):string{
        return JWT::encode(
            [
                "sub" => $u->getCedula(),
                "roles" => $u->getRoles(),
                "iat" => time(),
                "exp" => time() + 3600
            ], $_ENV["JWT_SECRETO"], 'HS256'
        );   
    }

    /**
     * Verifica la validez de un token JWT
     * @return object Claims del token verificado
     */
    public static function verificar():object{
        $cab =  $_SERVER["HTTP_AUTHORIZATION"] ?? "";
        if (!preg_match('/^Bearer (.+)$/', $cab, $matches)) {
            RespuestaJson::error(Traductor::t("comun.errorSesionNoIniciadada"), 401);
        }
        try{
            return JWT::decode($matches[1], new Key($_ENV["JWT_SECRETO"], 'HS256'));

        }catch (Exception $e){
            RespuestaJson::error(Traductor::t("comun.errorSesionNoIniciadada"), 401);
        }

}
}