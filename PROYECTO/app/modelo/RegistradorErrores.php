<?php

/**
 * Guarda el detalle real de un error en el log del servidor,
 * para que al usuario nunca se le muestre ese detalle directamente.
 */
class RegistradorErrores
{
    /**
     * Registra el error en el log interno del servidor.
     *
     * @param Exception $excepcion Excepción capturada.
     * @return void
     */
    public static function registrar(Exception $excepcion): void
    {
        $mensaje = "Error: " . $excepcion->getMessage() . " en " . $excepcion->getFile() . " linea " . $excepcion->getLine();
        error_log($mensaje);
    }
}

?>
