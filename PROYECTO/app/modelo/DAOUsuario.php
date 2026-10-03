<?php
 
/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre USUARIO y sus roles: listar, obtener, crear,
 * actualizar y eliminar.
 *
 * Los roles viven en tres tablas (ADMINISTRADOR, TECNICO y DOCENTE),
 * por eso las escrituras se hacen dentro de una transacción.
 * Nunca devuelve el hash de la contraseña.
 */
class DAOUsuario
{
    /** Relación entre el nombre del rol en la API y su tabla. */
    private const TABLAS_ROL = [
        "coordinador" => "ADMINISTRADOR",
        "tecnico" => "TECNICO",
        "docente" => "DOCENTE"
    ];
 
    private PDO $conexion;
 
    /**
     * Inicializa el DAO con una conexión activa a la base de datos.
     *
     * @param PDO $conexion Conexión PDO ya establecida.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }
 
    /**
     * Devuelve los nombres de rol permitidos.
     *
     * @return array Lista con coordinador, tecnico y docente.
     */
    public static function rolesPermitidos(): array
    {
        return array_keys(self::TABLAS_ROL);
    }
 
    /**
     * Recupera todos los usuarios junto con sus roles.
     *
     * @return array Lista de usuarios con cedula, nombre, apellido, activo y roles.
     */
    public function listar(): array
    {
        $consulta = $this->conexion->prepare($this->consultaBase() . " ORDER BY u.cedula ASC");
        $consulta->execute();
 
        return array_map([$this, "armarUsuario"], $consulta->fetchAll(PDO::FETCH_ASSOC));
    }
 
    /**
     * Recupera un usuario puntual a partir de su cédula.
     *
     * @param string $cedula Cédula del usuario.
     * @return array|null Los datos del usuario, o null si no existe.
     */
    public function obtener(string $cedula): ?array
    {
        $consulta = $this->conexion->prepare($this->consultaBase() . " WHERE u.cedula = :cedula");
        $consulta->execute([":cedula" => $cedula]);
 
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
 
        return $fila === false ? null : $this->armarUsuario($fila);
    }
 
    /**
     * Crea un usuario activo con sus roles.
     *
     * @param array $datos Arreglo con las claves cedula, nombre, apellido,
     *                     claveHash y roles.
     * @return bool true si se ejecutó correctamente.
     * @throws Throwable Si falla alguna inserción (se revierte todo).
     */
    public function crear(array $datos): bool
    {
        return $this->enTransaccion(function () use ($datos) {
            $consulta = $this->conexion->prepare("
                INSERT INTO USUARIO (cedula, clave, nombre, apellido, activo)
                VALUES (:cedula, :clave, :nombre, :apellido, 1)
            ");
            $consulta->execute([
                ":cedula" => $datos["cedula"],
                ":clave" => $datos["claveHash"],
                ":nombre" => $datos["nombre"],
                ":apellido" => $datos["apellido"]
            ]);
 
            foreach ($datos["roles"] as $rol) {
                $this->asignarRol($datos["cedula"], $rol);
            }
 
            return true;
        });
    }
 
    /**
     * Actualiza solo los campos presentes en $cambios.
     *
     * @param string $cedula Cédula del usuario a actualizar.
     * @param array $cambios Claves opcionales: nombre, apellido, claveHash,
     *                       activo (bool) y roles (array).
     * @return bool true si se ejecutó correctamente.
     * @throws Throwable Si falla alguna operación (se revierte todo).
     */
    public function actualizar(string $cedula, array $cambios): bool
    {
        return $this->enTransaccion(function () use ($cedula, $cambios) {
            $campos = [];
            $parametros = [":cedula" => $cedula];
 
            foreach (["nombre", "apellido"] as $campo) {
                if (array_key_exists($campo, $cambios)) {
                    $campos[] = "$campo = :$campo";
                    $parametros[":$campo"] = $cambios[$campo];
                }
            }
 
            if (array_key_exists("claveHash", $cambios)) {
                $campos[] = "clave = :clave";
                $parametros[":clave"] = $cambios["claveHash"];
            }
 
            if (array_key_exists("activo", $cambios)) {
                $campos[] = "activo = :activo";
                $parametros[":activo"] = $cambios["activo"] ? 1 : 0;
            }
 
            if (!empty($campos)) {
                $consulta = $this->conexion->prepare("UPDATE USUARIO SET " . implode(", ", $campos) . " WHERE cedula = :cedula");
                $consulta->execute($parametros);
            }
 
            if (array_key_exists("roles", $cambios)) {
                $this->sincronizarRoles($cedula, $cambios["roles"]);
            }
 
            return true;
        });
    }
 
    /**
     * Elimina un usuario y sus filas de rol.
     *
     * @param string $cedula Cédula del usuario a eliminar.
     * @return bool true si se ejecutó correctamente.
     * @throws Throwable Si el usuario tiene registros asociados (se revierte todo).
     */
    public function eliminar(string $cedula): bool
    {
        return $this->enTransaccion(function () use ($cedula) {
            foreach (self::TABLAS_ROL as $tabla) {
                $this->conexion->prepare("DELETE FROM $tabla WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
            }
 
            $this->conexion->prepare("DELETE FROM USUARIO WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
 
            return true;
        });
    }
 
    /**
     * Consulta base que une USUARIO con las tres tablas de rol.
     *
     * @return string Sentencia SELECT sin WHERE ni ORDER BY.
     */
    private function consultaBase(): string
    {
        return "
            SELECT
                u.cedula,
                u.nombre,
                u.apellido,
                u.activo,
                (a.cedula IS NOT NULL) AS esCoordinador,
                (t.cedula IS NOT NULL) AS esTecnico,
                (d.cedula IS NOT NULL) AS esDocente
            FROM USUARIO AS u
            LEFT JOIN ADMINISTRADOR AS a ON a.cedula = u.cedula
            LEFT JOIN TECNICO AS t ON t.cedula = u.cedula
            LEFT JOIN DOCENTE AS d ON d.cedula = u.cedula
        ";
    }
 
    /**
     * Convierte una fila de la consulta base en el formato del contrato JSON.
     *
     * @param array $fila Fila asociativa de la base de datos.
     * @return array Usuario con cedula, nombre, apellido, activo y roles.
     */
    private function armarUsuario(array $fila): array
    {
        $roles = [];
 
        if ((int) $fila["esCoordinador"] === 1) {
            $roles[] = "coordinador";
        }
        if ((int) $fila["esTecnico"] === 1) {
            $roles[] = "tecnico";
        }
        if ((int) $fila["esDocente"] === 1) {
            $roles[] = "docente";
        }
 
        return [
            "cedula" => $fila["cedula"],
            "nombre" => $fila["nombre"],
            "apellido" => $fila["apellido"],
            "activo" => (bool) $fila["activo"],
            "roles" => $roles
        ];
    }
 
    /**
     * Deja al usuario con exactamente los roles indicados.
     *
     * Solo inserta los que faltan y borra los que sobran, para no tocar
     * la fila de un rol que se mantiene (otras tablas la referencian
     * por clave foránea, por ejemplo los diagnósticos de un técnico).
     *
     * @param string $cedula Cédula del usuario.
     * @param array $rolesNuevos Roles que debe tener al terminar.
     * @return void
     */
    private function sincronizarRoles(string $cedula, array $rolesNuevos): void
    {
        foreach (self::TABLAS_ROL as $rol => $tabla) {
            $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM $tabla WHERE cedula = :cedula");
            $consulta->execute([":cedula" => $cedula]);
            $loTiene = ((int) $consulta->fetchColumn()) > 0;
            $loQuiere = in_array($rol, $rolesNuevos, true);
 
            if ($loQuiere && !$loTiene) {
                $this->asignarRol($cedula, $rol);
            } elseif (!$loQuiere && $loTiene) {
                $this->conexion->prepare("DELETE FROM $tabla WHERE cedula = :cedula")->execute([":cedula" => $cedula]);
            }
        }
    }
 
    /**
     * Inserta al usuario en la tabla del rol indicado.
     *
     * @param string $cedula Cédula del usuario.
     * @param string $rol Rol (coordinador, tecnico o docente).
     * @return void
     */
    private function asignarRol(string $cedula, string $rol): void
    {
        $tabla = self::TABLAS_ROL[$rol];
        $this->conexion->prepare("INSERT INTO $tabla (cedula) VALUES (:cedula)")->execute([":cedula" => $cedula]);
    }
 
    /**
     * Ejecuta una operación dentro de una transacción.
     *
     * @param callable $operacion Operación a ejecutar.
     * @return bool Resultado de la operación.
     * @throws Throwable Se vuelve a lanzar luego de revertir la transacción.
     */
    private function enTransaccion(callable $operacion): bool
    {
        $this->conexion->beginTransaction();
 
        try {
            $resultado = $operacion();
            $this->conexion->commit();
 
            return $resultado;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
 
            throw $e;
        }
    }
}
 
?>