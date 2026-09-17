<?php

/**
 * Vista de la API.
 *
 * En lugar de devolver HTML, esta vista devuelve JSON.
 * Todos sus métodos son estáticos, asi que se usan directamente
 * con RespuestaJson::exito o RespuestaJson::error sin crear un objeto.
 */
class RespuestaJson
{
    /**
     * Envía una respuesta JSON al cliente y termina la ejecución.
     *
     * @param int $codigo Código de estado HTTP.
     * @param array $cuerpo Contenido que se va a convertir a JSON.
     * @return void
     */
    public static function enviar(int $codigo, array $cuerpo): void
    {
        http_response_code($codigo);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envía una respuesta correcta.
     *
     * @param mixed $datos Datos solicitados (un arreglo, un registro, null).
     * @param string $mensaje Mensaje descriptivo para el cliente.
     * @param int $codigo Código de estado HTTP (200 por defecto, 201 al crear).
     * @return void
     */
    public static function exito($datos = null, string $mensaje = "Operación realizada correctamente.", int $codigo = 200): void
    {
        self::enviar($codigo, [
            "exito" => true,
            "mensaje" => $mensaje,
            "datos" => $datos
        ]);
    }

    /**
     * Envía una respuesta de error.
     *
     * @param string $mensaje Descripción del error.
     * @param int $codigo Código de estado HTTP (400 por defecto).
     * @return void
     */
    public static function error(string $mensaje, int $codigo = 400): void
    {
        self::enviar($codigo, [
            "exito" => false,
            "mensaje" => $mensaje,
            "datos" => null
        ]);
    }
}

?>