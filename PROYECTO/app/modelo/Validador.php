<?php

/**
 * Agrupa las reglas de validación de campos para que todos los
 * controladores formularios y API REST las apliquen
 * de la misma manera.
 */
class Validador
{
    /**
     * Verifica que el valor no esté vacío.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string El valor limpio.
     */
    public static function requerido($valor, string $campo): string
    {
        $valor = trim($valor ?? "");

        if ($valor === "") {
            throw new Exception("El campo $campo es obligatorio.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor tenga una longitud dentro del rango.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param int $minimo Cantidad mínima de caracteres.
     * @param int $maximo Cantidad máxima de caracteres.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string El valor limpio.
     */
    public static function longitud($valor, int $minimo, int $maximo, string $campo): string
    {
        $valor = trim($valor ?? "");

        if (strlen($valor) < $minimo || strlen($valor) > $maximo) {
            throw new Exception("El campo $campo debe tener entre $minimo y $maximo caracteres.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea una cédula válida (8 dígitos numéricos).
     *
     * @param mixed $valor Valor recibido del cliente.
     * @return string La cédula con trim.
     */
    public static function cedula($valor): string
    {
        $valor = trim($valor ?? "");

        if (strlen($valor) !== 8 || !ctype_digit($valor)) {
            throw new Exception("La cédula debe tener 8 dígitos numéricos.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea un número entero (solo dígitos).
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string El valor ya limpio (trim).
     */
    public static function numerico($valor, string $campo): string
    {
        $valor = trim($valor ?? "");

        if ($valor === "" || !ctype_digit($valor)) {
            throw new Exception("El campo $campo debe ser un número.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea uno de los permitidos.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param array $listaValida Valores permitidos para el campo.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string El valor "").
     */
    public static function enLista($valor, array $listaValida, string $campo): string
    {
        $valor = trim($valor ?? "");

        if (!in_array($valor, $listaValida, true)) {
            throw new Exception("El campo $campo tiene un valor no permitido.", 400);
        }

        return $valor;
    }
}

?>
