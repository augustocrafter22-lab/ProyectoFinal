<?php

class AltaDatosUsuario
{
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
     * @param string $claveHasheada Contraseña ya hasheada a almacenar.
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param int $activo Estado inicial del usuario (1 activo, 0 inactivo).
     * @param array $roles Roles a asignar al usuario.
     * @return bool true si el usuario fue creado correctamente.
     */
    public function crearUsuario(string $cedula, string $claveHasheada, string $nombre, string $apellido, int $activo, array $roles): bool
    {
        $rolesValidos = [
            "coordinador" => "administrador",
            "tecnico" => "tecnico",
            "docente" => "docente"
        ];
        // Validaciones

        // Valida que el nombre no este vacio
        if(trim($nombre) === ""){
            throw new Exception("El nombre no puede estar vacío");
        }

        // Valida que el apellido no este vacio
        if(trim($apellido) === ""){
            throw new Exception("El apellido no puede estar vacío");
        }
        // Vlida que la contraseña no tenga espacios al final
        if(trim($claveHasheada) === chop($claveHasheada)) {
            throw new Exception("La contraseña no puede tener espacios al final");
        }

        foreach ($roles as $rol => $value) {
            if (!isset($rolesValidos[$rol])) {
                throw new Exception("Rol no válido");
            }
        }

        try {
            $this->conexion->beginTransaction();

            // Insertar en USUARIO
            $sql = "INSERT INTO USUARIO (cedula, clave, nombre, apellido, activo) VALUES (:cedula, :clave, :nombre, :apellido, :activo)";
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                ":cedula" => $cedula,
                ":clave" => $claveHasheada,
                ":nombre" => $nombre,
                ":apellido" => $apellido,
                ":activo" => $activo
            ]);

            foreach ($roles as $rol) {
                $tablaRol = $rolesValidos[$rol];
                $sql = "INSERT INTO $tablaRol (cedula) VALUES (:cedula)";
                $this->conexion->prepare($sql)->execute([":cedula" => $cedula]);
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza los datos y/o roles de un usuario existente, actualizando solo los campos no nulos.
     *
     * @param string $cedula Cédula del usuario a actualizar.
     * @param string|null $nombre Nuevo nombre, o null para no modificarlo.
     * @param string|null $apellido Nuevo apellido, o null para no modificarlo.
     * @param string|null $clave Nueva contraseña en texto plano, o null para no modificarla.
     * @param array|null $roles Nuevos roles a asignar, o null para no modificarlos.
     * @param int|null $activo Nuevo estado del usuario, o null para no modificarlo.
     * @return bool true si la actualización fue exitosa.
     */
    public function actualizarUsuario(string $cedula, string $nombre = null, string $apellido = null, string $clave = null, array $roles = null, int $activo = null): bool
    {
        $rolesValidos = [
            "coordinador" => "administrador",
            "tecnico" => "tecnico",
            "docente" => "docente"
        ];

    // Si se está actualizando nombre, no puede quedar vacío
    if ($nombre !== null && trim($nombre) === "") {
        throw new Exception("El nombre es obligatorio");
    }
    // Si se está actualizando apellido, no puede quedar vacío
    if ($apellido !== null && trim($apellido) === "") {
        throw new Exception("El apellido es obligatorio");
    }

    // Si se está actualizando roles, tiene que quedar al menos uno
    if ($roles !== null) {
        if (empty($roles)) {
            throw new Exception("Debe seleccionar al menos un rol");
        }
        foreach ($roles as $rol) {
            if (!isset($rolesValidos[$rol])) {
                throw new Exception("Rol no válido: " . $rol);
            }
        }
    }

        if ($roles !== null) {
            foreach ($roles as $rol) {
                if (!isset($rolesValidos[$rol])) {
                    throw new Exception("Rol no válido");
                }
            }
        }
        try {
            $this->conexion->beginTransaction();

            // Actualizar USUARIO
            $updates = [];
            $params = [":cedula" => $cedula];

            if ($clave !== null) {
                $updates[] = "clave = :clave";
                $params[":clave"] = password_hash($clave, PASSWORD_BCRYPT);
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
                // Eliminar de todas las tablas de rol
                $this->conexion->prepare("DELETE FROM ADMINISTRADOR WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
                $this->conexion->prepare("DELETE FROM TECNICO WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
                $this->conexion->prepare("DELETE FROM DOCENTE WHERE cedula = :cedula")->execute([":cedula" => $cedula]);

                // Insertar en nuevos roles
                foreach ($roles as $rol) {
                 $tablaRol = $rolesValidos[$rol];
                $sql = "INSERT INTO $tablaRol (cedula) VALUES (:cedula)";
                $this->conexion->prepare($sql)->execute([":cedula" => $cedula]);
                }
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
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
}


