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
        $valor = self::limpiar($valor);

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
        $valor = self::limpiar($valor);

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
        $valor = self::limpiar($valor);

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
        $valor = self::limpiar($valor);

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
     * @return string El valor ya limpio (trim).
     */
    public static function enLista($valor, array $listaValida, string $campo): string
    {
        $valor = self::limpiar($valor);

        if (!in_array($valor, $listaValida, true)) {
            throw new Exception("El campo $campo tiene un valor no permitido.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea una fecha real con formato AAAA-MM-DD.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string La fecha ya limpia (trim).
     */
    public static function fecha($valor, string $campo): string
    {
        $valor = self::limpiar($valor);

        // checkdate comprueba que el día exista, por ejemplo rechaza 2026-02-31.
        if (!preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $valor, $partes) || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            throw new Exception("El campo $campo debe ser una fecha válida con formato AAAA-MM-DD.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea una hora válida con formato HH:MM o HH:MM:SS.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return string La hora ya limpia (trim).
     */
    public static function hora($valor, string $campo): string
    {
        $valor = self::limpiar($valor);

        if (!preg_match("/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $valor)) {
            throw new Exception("El campo $campo debe ser una hora válida con formato HH:MM.", 400);
        }

        return $valor;
    }

    /**
     * Verifica que el valor sea verdadero o falso y lo convierte al número
     * que guarda la base de datos.
     *
     * @param mixed $valor Valor recibido del cliente (true, false, 1, 0 o sus textos).
     * @param string $campo Nombre del campo, para el mensaje de error.
     * @return int 1 si es verdadero, 0 si es falso.
     */
    public static function booleano($valor, string $campo): int
    {
        // trim convierte false en "", por eso los booleanos se resuelven antes.
        if (is_bool($valor)) {
            return $valor ? 1 : 0;
        }

        $valor = strtolower(self::limpiar($valor));

        if ($valor === "1" || $valor === "true") {
            return 1;
        }

        if ($valor === "0" || $valor === "false") {
            return 0;
        }

        throw new Exception("El campo $campo debe ser true o false.", 400);
    }

    /**
     * Convierte el valor recibido a texto con trim. Si el cliente manda algo
     * que no es texto ni número (por ejemplo un arreglo) devuelve "", para que
     * la validación lo rechace con un error 400 en lugar de romper el programa.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @return string El valor como texto, con trim.
     */
    public static function limpiar($valor): string
    {
        return is_scalar($valor) ? trim((string) $valor) : "";
    }
}

?>
