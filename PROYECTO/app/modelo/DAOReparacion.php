<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre REPARACION.
 */
class DAOReparacion
{
    private PDO $conexion;

    /**
     * @param PDO $conexion Conexión PDO ya establecida.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Recupera las reparaciones registradas, opcionalmente filtradas por equipo.
     * Filtrar por equipo es lo que permite consultar el historial técnico de un equipo puntual.
     *
     * @param string|null $idEquipo Id del equipo a filtrar, o null para traer todas.
     * @return array Arreglo con los datos de cada reparación.
     */
    public function listar(?string $idEquipo = null): array
    {
        $sql = "
            SELECT
                idReparacion,
                idDiagnostico,
                idTicket,
                idEquipo,
                cedulaTecnico,
                reparacion,
                fechaReparacion
            FROM REPARACION
        ";

        if ($idEquipo !== null) {
            $sql .= " WHERE idEquipo = :idEquipo ";
        }

        $sql .= " ORDER BY fechaReparacion DESC ";

        $consulta = $this->conexion->prepare($sql);

        if ($idEquipo !== null) {
            $consulta->execute([":idEquipo" => $idEquipo]);
        } else {
            $consulta->execute();
        }

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera una reparación puntual a partir de su id.
     *
     * @param int $idReparacion Id de la reparación.
     * @return array|null Los datos de la reparación, o null si no existe.
     */
    public function obtener(int $idReparacion): ?array
    {
        $sql = "
            SELECT
                idReparacion,
                idDiagnostico,
                idTicket,
                idEquipo,
                cedulaTecnico,
                reparacion,
                fechaReparacion
            FROM REPARACION
            WHERE idReparacion = :idReparacion
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idReparacion" => $idReparacion]);

        $reparacion = $consulta->fetch(PDO::FETCH_ASSOC);

        return $reparacion === false ? null : $reparacion;
    }

    /**
     * Registra una nueva reparación a partir de un diagnóstico existente.
     * El ticket y el equipo se completan solos siguiendo la cadena
     * diagnóstico -> ticket -> equipo.
     *
     * @param array $datos Arreglo con las claves idDiagnostico, cedulaTecnico y reparacion.
     * @return int El idReparacion generado.
     */
    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO REPARACION
                (idDiagnostico, idTicket, idEquipo, cedulaTecnico, reparacion)
            SELECT
                d.idDiagnostico,
                d.idTicket,
                t.equipo,
                :cedulaTecnico,
                :reparacion
            FROM DIAGNOSTICO AS d
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
            WHERE d.idDiagnostico = :idDiagnostico
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idDiagnostico" => $datos["idDiagnostico"],
            ":cedulaTecnico" => $datos["cedulaTecnico"],
            ":reparacion" => $datos["reparacion"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Actualiza el texto de una reparación existente.
     *
     * @param int $idReparacion Id de la reparación a actualizar.
     * @param array $datos Arreglo con la clave reparacion.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(int $idReparacion, array $datos): bool
    {
        $sql = "
            UPDATE REPARACION
            SET reparacion = :reparacion
            WHERE idReparacion = :idReparacion
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":reparacion" => $datos["reparacion"],
            ":idReparacion" => $idReparacion
        ]);
    }

    /**
     * Elimina una reparación de la base de datos.
     *
     * @param int $idReparacion Id de la reparación a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(int $idReparacion): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM REPARACION WHERE idReparacion = :idReparacion");

        return $consulta->execute([":idReparacion" => $idReparacion]);
    }

    /**
     * Verifica si un diagnóstico existe, para no registrar reparaciones sueltas.
     *
     * @param int $idDiagnostico Id del diagnóstico.
     * @return bool true si el diagnóstico existe.
     */
    public function existeDiagnostico(int $idDiagnostico): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM DIAGNOSTICO WHERE idDiagnostico = :idDiagnostico");
        $consulta->execute([":idDiagnostico" => $idDiagnostico]);

        return ((int) $consulta->fetchColumn()) > 0;
    }
}

?>
