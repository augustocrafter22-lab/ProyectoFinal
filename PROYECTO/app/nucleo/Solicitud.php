<?php

/**
 * Lee los datos que el cliente envía en el cuerpo de la solicitud HTTP,
 * así todos los controladores los obtienen de la misma manera.
 */
class Solicitud
{
    /**
     * Obtiene los datos enviados por el cliente.
     *
     * Lo normal es JSON (lo que manda fetch), pero también acepta los datos
     * de un formulario $_POST o el formato clave=valor de un PUT o DELETE.
     *
     * @return array Arreglo asociativo con los datos recibidos.
     */
    public static function obtenerDatos(): array
    {
        $cuerpo = file_get_contents("php://input");
        $datos = json_decode($cuerpo, true);

        if (is_array($datos)) {
            return $datos;
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        parse_str($cuerpo, $datosFormulario);

        return $datosFormulario;
    }
}
