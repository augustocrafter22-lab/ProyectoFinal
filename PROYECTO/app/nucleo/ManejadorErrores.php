<?php

require_once RUTA_NUCLEO . "/RegistradorErrores.php";
require_once RUTA_NUCLEO . "/Traductor.php";
require_once RUTA_VISTA . "/RespuestaJson.php";

/**
 * Único lugar donde se convierte una excepción en una respuesta JSON.
 *
 * Los controladores solo lanzan excepciones; el punto de entrada
 * (public/index.php) las captura y se las entrega a esta clase.
 */
class ManejadorErrores
{
    /**
     * Responde al cliente según el tipo de error y termina la ejecución.
     *
     * - Errores de la base de datos: se registran y se responde un 500 genérico
     *   (o un 409 si no se pudo eliminar por tener registros relacionados).
     * - Excepciones con un código HTTP entre 400 y 499: el mensaje es para el cliente.
     * - Cualquier otro error: se registra y se responde un 500 genérico.
     *
     * @param Throwable $error Excepción o error capturado.
     * @return void
     */
    public static function responder(Throwable $error): void
    {
        if ($error instanceof PDOException) {
            self::responderErrorBaseDeDatos($error);
        }

        $codigo = (int) $error->getCode();

        if ($error instanceof Exception && $codigo >= 400 && $codigo <= 499) {
            RespuestaJson::error($error->getMessage(), $codigo);
        }

        RegistradorErrores::registrar($error);
        RespuestaJson::error(Traductor::t("comun.errorGenerico"), 500);
    }

    /**
     * Responde a un error de PDO sin mostrar el detalle de la base de datos.
     *
     * Las restricciones de integridad (SQLSTATE 23000) se distinguen por el código
     * propio de MariaDB, porque cada una significa algo distinto para el usuario.
     *
     * @param PDOException $error Error lanzado por PDO.
     * @return void
     */
    private static function responderErrorBaseDeDatos(PDOException $error): void
    {
        RegistradorErrores::registrar($error);

        $codigoMariaDb = (int) ($error->errorInfo[1] ?? 0);

        // 1451: el registro tiene datos relacionados y la base no deja borrarlo o cambiarlo.
        if ($codigoMariaDb === 1451) {
            RespuestaJson::error(Traductor::t("comun.errorRegistrosRelacionados"), 409);
        }

        // 1062: ya existe un registro con ese identificador.
        if ($codigoMariaDb === 1062) {
            RespuestaJson::error(Traductor::t("comun.errorRegistroDuplicado"), 409);
        }

        // 1452: se hace referencia a un registro que no existe (por ejemplo un equipo inexistente).
        if ($codigoMariaDb === 1452) {
            RespuestaJson::error(Traductor::t("comun.errorReferenciaInexistente"), 400);
        }

        RespuestaJson::error(Traductor::t("comun.errorGenerico"), 500);
    }
}
