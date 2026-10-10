<?php

require_once RUTA_MODELO . "/Validador.php";

/**
 * Valida los datos de entrada específicos de las operaciones de usuario.
 */
class ValidadorUsuario
{
    private const ROLES_VALIDOS = ["coordinador", "tecnico", "docente"];

    /**
     * Valida y normaliza los datos requeridos para registrar un usuario.
     *
     * @param array $datos Datos recibidos en la solicitud.
     * @return array Datos validados para la creación.
     */
    public static function registro(array $datos): array
    {
        return [
            "cedula" => self::cedula($datos["cedula"] ?? ""),
            "nombre" => Validador::longitud(self::texto($datos["nombre"] ?? ""), 1, 12, "nombre"),
            "apellido" => Validador::longitud(self::texto($datos["apellido"] ?? ""), 1, 16, "apellido"),
            "clave" => Validador::requerido(self::texto($datos["clave"] ?? ""), "clave"),
            "roles" => self::roles($datos["roles"] ?? null)
        ];
    }

    /**
     * Valida y normaliza los cambios opcionales de un usuario.
     *
     * @param array $datos Datos recibidos en la solicitud.
     * @param string $cedulaSesion Cédula del usuario autenticado.
     * @param string $cedula Cédula del usuario modificado.
     * @return array Cambios validados.
     */
    public static function cambios(array $datos, string $cedulaSesion, string $cedula): array
    {
        $cambios = [];

        if (array_key_exists("nombre", $datos)) {
            $cambios["nombre"] = Validador::longitud(self::texto($datos["nombre"]), 1, 12, "nombre");
        }

        if (array_key_exists("apellido", $datos)) {
            $cambios["apellido"] = Validador::longitud(self::texto($datos["apellido"]), 1, 16, "apellido");
        }

        if (array_key_exists("clave", $datos)) {
            $cambios["clave"] = Validador::requerido(self::texto($datos["clave"]), "clave");
        }

        if (array_key_exists("roles", $datos)) {
            $cambios["roles"] = self::roles($datos["roles"]);
        }

        if (array_key_exists("activo", $datos)) {
            $cambios["activo"] = self::activo($datos["activo"]);
        }

        if (empty($cambios)) {
            throw new Exception("Debe enviar al menos un campo para modificar.", 400);
        }

        if ($cedula === $cedulaSesion) {
            if (isset($cambios["activo"]) && $cambios["activo"] === 0) {
                throw new Exception("No puede desactivar su propio usuario.", 409);
            }

            if (isset($cambios["roles"]) && !in_array("coordinador", $cambios["roles"], true)) {
                throw new Exception("No puede quitarse a si mismo el rol coordinador.", 409);
            }
        }

        return $cambios;
    }

    /**
     * Valida una cédula.
     *
     * @param mixed $valor Valor recibido.
     * @return string Cédula validada.
     */
    public static function cedula($valor): string
    {
        return Validador::cedula(self::texto($valor));
    }

    /**
     * Limpia un valor escalar sin convertir arreglos u objetos a texto.
     *
     * @param mixed $valor Valor recibido.
     * @return string Texto limpio o una cadena vacía.
     */
    public static function texto($valor): string
    {
        return is_scalar($valor) ? trim((string) $valor) : "";
    }

    /**
     * Valida y deduplica los roles recibidos.
     *
     * @param mixed $roles Roles recibidos.
     * @return array Roles permitidos.
     */
    private static function roles($roles): array
    {
        if (!is_array($roles) || empty($roles)) {
            throw new Exception("Debe indicar al menos un rol.", 400);
        }

        $rolesValidados = [];

        foreach ($roles as $rol) {
            $rolesValidados[] = Validador::enLista(self::texto($rol), self::ROLES_VALIDOS, "roles");
        }

        return array_values(array_unique($rolesValidados));
    }

    /**
     * Valida el estado activo enviado por el cliente.
     *
     * @param mixed $activo Estado recibido.
     * @return int Estado normalizado para persistencia.
     */
    private static function activo($activo): int
    {
        if ($activo === true || $activo === 1 || $activo === "1") {
            return 1;
        }

        if ($activo === false || $activo === 0 || $activo === "0") {
            return 0;
        }

        throw new Exception("El campo activo debe ser true o false.", 400);
    }
}

?>
