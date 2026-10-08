<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre PRESTAMO: listar, obtener, crear, devolver, cambiar la
 * fecha de devolución estimada y eliminar.
 *
 * Un préstamo está "Activo" hasta que se devuelve el equipo y pasa a
 * "Devuelto". Mientras está activo el equipo figura como "No disponible",
 * por eso crear y devolver modifican también la tabla EQUIPO dentro de
 * una transacción.
 */
class DAOPrestamo
{
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
     * Recupera los préstamos registrados, opcionalmente filtrados,
     * del más nuevo al más viejo.
     *
     * @param array $filtros Claves opcionales: estado e idEquipo.
     * @return array Arreglo asociativo con los datos de cada préstamo.
     */
    public function listar(array $filtros = []): array
    {
        $condiciones = [];
        $parametros = [];

        if (($filtros["estado"] ?? "") !== "") {
            $condiciones[] = "p.estado = :estado";
            $parametros[":estado"] = $filtros["estado"];
        }

        if (($filtros["idEquipo"] ?? "") !== "") {
            $condiciones[] = "p.idEquipo = :idEquipo";
            $parametros[":idEquipo"] = $filtros["idEquipo"];
        }

        $sql = $this->consultaBase();

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY p.fechaPrestamo DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un préstamo puntual a partir de su id.
     *
     * @param int $idPrestamo Id del préstamo.
     * @return array|null Los datos del préstamo, o null si no existe.
     */
    public function obtener(int $idPrestamo): ?array
    {
        $consulta = $this->conexion->prepare($this->consultaBase() . " WHERE p.idPrestamo = :idPrestamo");
        $consulta->execute([":idPrestamo" => $idPrestamo]);

        $prestamo = $consulta->fetch(PDO::FETCH_ASSOC);

        return $prestamo === false ? null : $prestamo;
    }

    /**
     * Registra un préstamo nuevo y deja el equipo como "No disponible".
     * Si el equipo ya no estaba disponible no se registra nada.
     *
     * @param array $datos Arreglo con las claves idEquipo, cedulaSolicitante
     *                     y fechaDevolucionEstimada (fecha y hora).
     * @return int El idPrestamo generado, o 0 si el equipo no estaba disponible.
     */
    public function crear(array $datos): int
    {
        try {
            $this->conexion->beginTransaction();

            // El UPDATE solo afecta al equipo si sigue disponible. Así, si dos personas
            // piden el mismo equipo a la vez, solo una lo consigue.
            $consulta = $this->conexion->prepare("
                UPDATE EQUIPO
                SET disponibilidad = 'No disponible'
                WHERE idEquipo = :idEquipo AND disponibilidad = 'Disponible'
            ");
            $consulta->execute([":idEquipo" => $datos["idEquipo"]]);

            if ($consulta->rowCount() === 0) {
                $this->conexion->rollBack();
                return 0;
            }

            $consulta = $this->conexion->prepare("
                INSERT INTO PRESTAMO (idEquipo, cedulaSolicitante, fechaDevolucionEstimada)
                VALUES (:idEquipo, :cedulaSolicitante, :fechaDevolucionEstimada)
            ");
            $consulta->execute([
                ":idEquipo" => $datos["idEquipo"],
                ":cedulaSolicitante" => $datos["cedulaSolicitante"],
                ":fechaDevolucionEstimada" => $datos["fechaDevolucionEstimada"]
            ]);

            $idPrestamo = (int) $this->conexion->lastInsertId();

            $this->conexion->commit();

            return $idPrestamo;

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Registra la devolución de un préstamo activo: guarda la fecha real y
     * deja el equipo como "Disponible", salvo que no esté funcionando
     * (por ejemplo si se dañó mientras estaba prestado).
     *
     * @param int $idPrestamo Id del préstamo a devolver.
     * @return bool true si se registró la devolución, false si el préstamo ya estaba devuelto.
     */
    public function devolver(int $idPrestamo): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("
                UPDATE PRESTAMO
                SET estado = 'Devuelto', fechaDevolucionReal = NOW()
                WHERE idPrestamo = :idPrestamo AND estado = 'Activo'
            ");
            $consulta->execute([":idPrestamo" => $idPrestamo]);

            if ($consulta->rowCount() === 0) {
                $this->conexion->rollBack();
                return false;
            }

            $consulta = $this->conexion->prepare("
                UPDATE EQUIPO AS e
                INNER JOIN PRESTAMO AS p ON p.idEquipo = e.idEquipo
                SET e.disponibilidad = 'Disponible'
                WHERE p.idPrestamo = :idPrestamo AND e.estado = 'Funcionando'
            ");
            $consulta->execute([":idPrestamo" => $idPrestamo]);

            $this->conexion->commit();

            return true;

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cambia la fecha de devolución estimada de un préstamo.
     *
     * @param int $idPrestamo Id del préstamo a modificar.
     * @param string $fechaDevolucionEstimada Nueva fecha y hora estimada.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizarFechaEstimada(int $idPrestamo, string $fechaDevolucionEstimada): bool
    {
        $consulta = $this->conexion->prepare("
            UPDATE PRESTAMO
            SET fechaDevolucionEstimada = :fechaDevolucionEstimada
            WHERE idPrestamo = :idPrestamo
        ");

        return $consulta->execute([
            ":fechaDevolucionEstimada" => $fechaDevolucionEstimada,
            ":idPrestamo" => $idPrestamo
        ]);
    }

    /**
     * Elimina un préstamo de la base de datos.
     *
     * @param int $idPrestamo Id del préstamo a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(int $idPrestamo): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM PRESTAMO WHERE idPrestamo = :idPrestamo");

        return $consulta->execute([":idPrestamo" => $idPrestamo]);
    }

    /**
     * Verifica si existe un equipo con el id indicado.
     *
     * @param string $idEquipo Id del equipo.
     * @return bool true si el equipo existe, false en caso contrario.
     */
    public function existeEquipo(string $idEquipo): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM EQUIPO WHERE idEquipo = :idEquipo");
        $consulta->execute([":idEquipo" => $idEquipo]);

        return ((int) $consulta->fetchColumn()) > 0;
    }

    /**
     * Verifica si existe un usuario con la cédula indicada.
     *
     * @param string $cedula Cédula del usuario.
     * @return bool true si el usuario existe, false en caso contrario.
     */
    public function existeUsuario(string $cedula): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM USUARIO WHERE cedula = :cedula");
        $consulta->execute([":cedula" => $cedula]);

        return ((int) $consulta->fetchColumn()) > 0;
    }

    /**
     * Consulta común de listar y obtener: el préstamo junto con el nombre
     * completo de quien lo solicitó.
     *
     * @return string Sentencia SQL sin WHERE ni ORDER BY.
     */
    private function consultaBase(): string
    {
        return "
            SELECT
                p.idPrestamo,
                p.idEquipo,
                p.cedulaSolicitante,
                CONCAT(u.nombre, ' ', u.apellido) AS solicitante,
                p.fechaPrestamo,
                p.fechaDevolucionEstimada,
                p.fechaDevolucionReal,
                p.estado
            FROM PRESTAMO AS p
            INNER JOIN USUARIO AS u ON u.cedula = p.cedulaSolicitante
        ";
    }
}

?>
