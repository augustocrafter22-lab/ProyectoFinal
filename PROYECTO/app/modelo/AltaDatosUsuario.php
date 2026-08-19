<?php

class AltaDatosUsuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function usuarioExiste(string $cedula): bool
    {
        $sql = "SELECT cedula FROM USUARIO WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["cedula" => $cedula]);
        return $consulta->fetch() !== false;
    }

    public function crearUsuario(string $cedula, string $claveHasheada, int $activo, string $rol): bool
    {
        try {
            $this->conexion->beginTransaction();

            // Insertar en USUARIO
            $sql = "INSERT INTO USUARIO (cedula, clave, activo) VALUES (:cedula, :clave, :activo)";
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                ":cedula" => $cedula,
                ":clave" => $claveHasheada,
                ":activo" => $activo
            ]);

            // Insertar en tabla según rol
            if ($rol === "coordinador") {
                $sql = "INSERT INTO ADMINISTRADOR (cedula) VALUES (:cedula)";
            } elseif ($rol === "tecnico") {
                $sql = "INSERT INTO TECNICO (cedula) VALUES (:cedula)";
            } elseif ($rol === "docente") {
                $sql = "INSERT INTO DOCENTE (cedula) VALUES (:cedula)";
            } else {
                throw new Exception("Rol no válido");
            }

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([":cedula" => $cedula]);

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function actualizarUsuario(string $cedula, string $clave = null, string $rol = null, int $activo = null): bool
    {
        try {
            $this->conexion->beginTransaction();

            // Actualizar USUARIO
            $updates = [];
            $params = [":cedula" => $cedula];

            if ($clave !== null) {
                $updates[] = "clave = :clave";
                $params[":clave"] = password_hash($clave, PASSWORD_BCRYPT);
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
            if ($rol !== null) {
                // Eliminar de todas las tablas de rol
                $this->conexion->prepare("DELETE FROM ADMINISTRADOR WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
                $this->conexion->prepare("DELETE FROM TECNICO WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
                $this->conexion->prepare("DELETE FROM DOCENTE WHERE cedula = :cedula")->execute([":cedula" => $cedula]);

                // Insertar en nuevo rol
                if ($rol === "coordinador") {
                    $sql = "INSERT INTO ADMINISTRADOR (cedula) VALUES (:cedula)";
                } elseif ($rol === "tecnico") {
                    $sql = "INSERT INTO TECNICO (cedula) VALUES (:cedula)";
                } elseif ($rol === "docente") {
                    $sql = "INSERT INTO DOCENTE (cedula) VALUES (:cedula)";
                } else {
                    throw new Exception("Rol no válido");
                }

                $this->conexion->prepare($sql)->execute([":cedula" => $cedula]);
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function desactivarUsuario(string $cedula): bool
    {
        $sql = "UPDATE USUARIO SET activo = 0 WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":cedula" => $cedula]);
    }
    public function activarUsuario(string $cedula): bool
    {
        $sql = "UPDATE USUARIO SET activo = 1 WHERE cedula = :cedula";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":cedula" => $cedula]);
    }
}

    
