<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre USUARIO y sus roles: listar, obtener, crear, actualizar
 * y eliminar.
 *
 * Los roles se guardan en tablas aparte (ADMINISTRADOR, TECNICO y DOCENTE),
 * por eso crear, actualizar y eliminar usan una transacción.
 */
class DAOUsuario
{
    private PDO $conexion;

    /** @var array Tabla de la base de datos que corresponde a cada rol. */
    private array $tablasRol = [
        "coordinador" => "ADMINISTRADOR",
        "tecnico" => "TECNICO",
        "docente" => "DOCENTE"
    ];

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
     * Recupera todos los usuarios junto con sus roles.
     * Nunca devuelve la contraseña.
     *
     * @return array Arreglo con cedula, nombre, apellido, activo y roles de cada usuario.
     */
    public function listar(): array
    {
        $consulta = $this->conexion->prepare($this->consultaBase() . " ORDER BY u.cedula ASC");
        $consulta->execute();

        $usuarios = [];

        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $usuarios[] = $this->armarUsuario($fila);
        }

        return $usuarios;
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
     * Registra un usuario nuevo y le asigna sus roles.
     * Si algo falla se deshace todo con rollBack().
     *
     * @param array $datos Arreglo con las claves cedula, nombre, apellido,
     *                     claveHash (ya hasheada), activo (1 o 0) y roles.
     * @return void
     */
    public function crear(array $datos): void
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "
                INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
                VALUES (:cedula, :nombre, :apellido, :clave, :activo)
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                ":cedula" => $datos["cedula"],
                ":nombre" => $datos["nombre"],
                ":apellido" => $datos["apellido"],
                ":clave" => $datos["claveHash"],
                ":activo" => $datos["activo"]
            ]);

            $this->guardarRoles($datos["cedula"], $datos["roles"]);

            $this->conexion->commit();

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Modifica solo los campos que vengan en $cambios, el resto queda igual.
     * Sirve para editar datos, cambiar roles, y activar o desactivar.
     *
     * @param string $cedula Cédula del usuario a modificar.
     * @param array $cambios Claves opcionales: nombre, apellido, claveHash
     *                       (ya hasheada), activo (1 o 0) y roles (arreglo).
     * @return void
     */
    public function actualizar(string $cedula, array $cambios): void
    {
        try {
            $this->conexion->beginTransaction();

            $campos = [];
            $parametros = [":cedula" => $cedula];

            if (isset($cambios["nombre"])) {
                $campos[] = "nombre = :nombre";
                $parametros[":nombre"] = $cambios["nombre"];
            }

            if (isset($cambios["apellido"])) {
                $campos[] = "apellido = :apellido";
                $parametros[":apellido"] = $cambios["apellido"];
            }

            if (isset($cambios["claveHash"])) {
                $campos[] = "clave = :clave";
                $parametros[":clave"] = $cambios["claveHash"];
            }

            if (isset($cambios["activo"])) {
                $campos[] = "activo = :activo";
                $parametros[":activo"] = $cambios["activo"];
            }

            if (!empty($campos)) {
                $sql = "UPDATE USUARIO SET " . implode(", ", $campos) . " WHERE cedula = :cedula";

                $consulta = $this->conexion->prepare($sql);
                $consulta->execute($parametros);
            }

            if (isset($cambios["roles"])) {
                $this->borrarRoles($cedula);
                $this->guardarRoles($cedula, $cambios["roles"]);
            }

            $this->conexion->commit();

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Elimina un usuario junto con sus roles.
     * Si el usuario tiene registros relacionados (por ejemplo reparaciones
     * como técnico) la base lo impide y se lanza una PDOException.
     *
     * @param string $cedula Cédula del usuario a eliminar.
     * @return void
     */
    public function eliminar(string $cedula): void
    {
        try {
            $this->conexion->beginTransaction();

            $this->borrarRoles($cedula);

            $consulta = $this->conexion->prepare("DELETE FROM USUARIO WHERE cedula = :cedula");
            $consulta->execute([":cedula" => $cedula]);

            $this->conexion->commit();

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Consulta común de listar y obtener: el usuario con sus roles en una
     * sola columna de texto ("coordinador, tecnico"), sin la contraseña.
     *
     * @return string Sentencia SQL sin WHERE ni ORDER BY.
     */
    private function consultaBase(): string
    {
        return "
            SELECT
                u.cedula,
                u.nombre,
                u.apellido,
                u.activo,
                CONCAT_WS(', ',
                    CASE WHEN a.cedula IS NOT NULL THEN 'coordinador' END,
                    CASE WHEN t.cedula IS NOT NULL THEN 'tecnico' END,
                    CASE WHEN d.cedula IS NOT NULL THEN 'docente' END
                ) AS roles
            FROM USUARIO AS u
            LEFT JOIN ADMINISTRADOR AS a ON a.cedula = u.cedula
            LEFT JOIN TECNICO AS t ON t.cedula = u.cedula
            LEFT JOIN DOCENTE AS d ON d.cedula = u.cedula
        ";
    }

    /**
     * Convierte una fila de la consulta en el usuario que se devuelve a la API:
     * activo pasa a booleano y roles pasa de texto a arreglo.
     *
     * @param array $fila Fila obtenida con consultaBase().
     * @return array Arreglo con cedula, nombre, apellido, activo y roles.
     */
    private function armarUsuario(array $fila): array
    {
        return [
            "cedula" => $fila["cedula"],
            "nombre" => $fila["nombre"],
            "apellido" => $fila["apellido"],
            "activo" => (bool) $fila["activo"],
            "roles" => $fila["roles"] === "" ? [] : explode(", ", $fila["roles"])
        ];
    }

    /**
     * Inserta al usuario en la tabla de cada rol indicado.
     * El nombre de la tabla sale siempre de $tablasRol, nunca del cliente.
     *
     * @param string $cedula Cédula del usuario.
     * @param array $roles Roles a asignar (coordinador, tecnico, docente).
     * @return void
     */
    private function guardarRoles(string $cedula, array $roles): void
    {
        foreach ($roles as $rol) {
            $tabla = $this->tablasRol[$rol];

            $consulta = $this->conexion->prepare("INSERT INTO $tabla (cedula) VALUES (:cedula)");
            $consulta->execute([":cedula" => $cedula]);
        }
    }

    /**
     * Quita al usuario de las tablas de todos los roles.
     *
     * @param string $cedula Cédula del usuario.
     * @return void
     */
    private function borrarRoles(string $cedula): void
    {
        foreach ($this->tablasRol as $tabla) {
            $consulta = $this->conexion->prepare("DELETE FROM $tabla WHERE cedula = :cedula");
            $consulta->execute([":cedula" => $cedula]);
        }
    }
}

?>
