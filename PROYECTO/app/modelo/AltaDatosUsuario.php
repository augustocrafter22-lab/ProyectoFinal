<?php

/**
 * Clase encargada de las operaciones de alta, edición, activación y
 * desactivación de usuarios. La contraseña siempre llega ya hasheada:
 * el hasheo lo hace el controlador, igual que en el login se compara el hash.
 */
class AltaDatosUsuario
{
    // Rol del sistema => tabla donde se guarda. Se define una sola vez para toda la clase
    private const ROLES_VALIDOS = [
        "coordinador" => "ADMINISTRADOR",
        "tecnico" => "TECNICO",
        "docente" => "DOCENTE"
    ];

    private PDO $conexion;

    /**
     * @param PDO $conexion Conexión activa a la base de datos.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Verifica si ya existe un usuario registrado con la cédula indicada.
     *
     * @param string $cedula Cédula a verificar.
     * @return bool true si el usuario existe, false en caso contrario.
     */
    public function usuarioExiste(string $cedula): bool
    {
        $sql = "SELECT cedula FROM USUARIO WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["cedula" => $cedula]);
        return $consulta->fetch() !== false;
    }

    /**
     * Crea un nuevo usuario con sus roles asociados, validando los datos ingresados.
     *
     * @param string $cedula Cédula del nuevo usuario.
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param string $claveHasheada Contraseña ya hasheada a almacenar.
     * @param int $activo Estado inicial del usuario (1 activo, 0 inactivo).
     * @param array $roles Roles a asignar al usuario.
     * @return bool true si el usuario fue creado correctamente.
     * @throws Exception Si algún dato no es válido o falla la base de datos.
     */
    public function crearUsuario(string $cedula, string $nombre, string $apellido, string $claveHasheada, int $activo, array $roles): bool
    {
        // Valida que el nombre no este vacio
        if (trim($nombre) === "") {
            throw new Exception("El nombre no puede estar vacío");
        }

        // Valida que el apellido no este vacio
        if (trim($apellido) === "") {
            throw new Exception("El apellido no puede estar vacío");
        }

        $this->validarClaveHasheada($claveHasheada);
        $this->validarRoles($roles);

        try {
            $this->conexion->beginTransaction();

            // Insertar en USUARIO (mismo orden que los parámetros del método y que la tabla)
            $sql = "INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo) VALUES (:cedula, :nombre, :apellido, :clave, :activo)";
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                ":cedula" => $cedula,
                ":nombre" => $nombre,
                ":apellido" => $apellido,
                ":clave" => $claveHasheada,
                ":activo" => $activo
            ]);

            $this->insertarRoles($cedula, $roles);

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->deshacerTransaccion();
            throw $e;
        }
    }

    /**
     * Actualiza los datos y/o roles de un usuario existente, actualizando solo los campos no nulos.
     *
     * @param string $cedula Cédula del usuario a actualizar.
     * @param string|null $nombre Nuevo nombre, o null para no modificarlo.
     * @param string|null $apellido Nuevo apellido, o null para no modificarlo.
     * @param string|null $claveHasheada Nueva contraseña ya hasheada, o null para no modificarla.
     * @param array|null $roles Nuevos roles a asignar, o null para no modificarlos.
     * @param int|null $activo Nuevo estado del usuario, o null para no modificarlo.
     * @return bool true si la actualización fue exitosa.
     * @throws Exception Si algún dato no es válido o falla la base de datos.
     */
    public function actualizarUsuario(string $cedula, ?string $nombre = null, ?string $apellido = null, ?string $claveHasheada = null, ?array $roles = null, ?int $activo = null): bool
    {
        // Si se está actualizando nombre, no puede quedar vacío
        if ($nombre !== null && trim($nombre) === "") {
            throw new Exception("El nombre es obligatorio");
        }
        // Si se está actualizando apellido, no puede quedar vacío
        if ($apellido !== null && trim($apellido) === "") {
            throw new Exception("El apellido es obligatorio");
        }

        if ($claveHasheada !== null) {
            $this->validarClaveHasheada($claveHasheada);
        }

        // Si se está actualizando roles, tiene que quedar al menos uno
        if ($roles !== null) {
            if (empty($roles)) {
                throw new Exception("Debe seleccionar al menos un rol");
            }
            $this->validarRoles($roles);
        }

        try {
            $this->conexion->beginTransaction();

            // Actualizar USUARIO
            $updates = [];
            $params = [":cedula" => $cedula];

            if ($claveHasheada !== null) {
                $updates[] = "clave = :clave";
                $params[":clave"] = $claveHasheada;
            }
            if ($nombre !== null) {
                $updates[] = "nombre = :nombre";
                $params[":nombre"] = $nombre;
            }
            if ($apellido !== null) {
                $updates[] = "apellido = :apellido";
                $params[":apellido"] = $apellido;
            }

            if ($activo !== null) {
                $updates[] = "activo = :activo";
                $params[":activo"] = $activo;
            }

            if (!empty($updates)) {
                $sql = "UPDATE USUARIO SET " . implode(", ", $updates) . " WHERE cedula = :cedula";
                $consulta = $this->conexion->prepare($sql);
                $consulta->execute($params);
            }

            // Actualizar rol si se proporciona
            if ($roles !== null) {
                // Eliminar de todas las tablas de rol y volver a insertar los nuevos
                foreach (self::ROLES_VALIDOS as $tablaRol) {
                    $this->conexion->prepare("DELETE FROM $tablaRol WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
                }
                $this->insertarRoles($cedula, $roles);
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->deshacerTransaccion();
            throw $e;
        }
    }

    /**
     * Desactiva un usuario existente marcándolo como inactivo.
     *
     * @param string $cedula Cédula del usuario a desactivar.
     * @return bool true si la operación fue exitosa.
     */
    public function desactivarUsuario(string $cedula): bool
    {
        $sql = "UPDATE USUARIO SET activo = 0 WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":cedula" => $cedula]);
    }

    /**
     * Activa un usuario existente marcándolo como activo.
     *
     * @param string $cedula Cédula del usuario a activar.
     * @return bool true si la operación fue exitosa.
     */
    public function activarUsuario(string $cedula): bool
    {
        $sql = "UPDATE USUARIO SET activo = 1 WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":cedula" => $cedula]);
    }

    /**
     * Verifica que la contraseña recibida sea un hash y no texto plano.
     * Así nunca se guarda una clave sin hashear, ni aunque se crucen los parámetros.
     *
     * @param string $claveHasheada Valor recibido como contraseña.
     * @throws Exception Si el valor no es un hash de password_hash().
     */
    private function validarClaveHasheada(string $claveHasheada): void
    {
        if (password_get_info($claveHasheada)["algo"] === null) {
            throw new Exception("La contraseña debe llegar hasheada");
        }
    }

    /**
     * Verifica que todos los roles recibidos existan en el sistema.
     *
     * @param array $roles Roles a validar.
     * @throws Exception Si algún rol no es válido.
     */
    private function validarRoles(array $roles): void
    {
        foreach ($roles as $rol) {
            if (!isset(self::ROLES_VALIDOS[$rol])) {
                throw new Exception("Rol no válido: " . $rol);
            }
        }
    }

    /**
     * Inserta al usuario en la tabla de cada uno de sus roles.
     * Los roles ya deben estar validados con validarRoles().
     *
     * @param string $cedula Cédula del usuario.
     * @param array $roles Roles a asignar.
     */
    private function insertarRoles(string $cedula, array $roles): void
    {
        foreach ($roles as $rol) {
            $tablaRol = self::ROLES_VALIDOS[$rol];
            $sql = "INSERT INTO $tablaRol (cedula) VALUES (:cedula)";
            $this->conexion->prepare($sql)->execute([":cedula" => $cedula]);
        }
    }

    /**
     * Deshace la transacción en curso, si hay una.
     */
    private function deshacerTransaccion(): void
    {
        if ($this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        }
    }
}
