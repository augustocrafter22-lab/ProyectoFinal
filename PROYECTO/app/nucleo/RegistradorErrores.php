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
     * @param Throwable $error Excepción o error capturado.
     * @return void
     */
    public static function registrar(Throwable $error): void
    {
        $mensaje = "Error: " . $error->getMessage() . " en " . $error->getFile() . " linea " . $error->getLine();
        error_log($mensaje);
    }
}
