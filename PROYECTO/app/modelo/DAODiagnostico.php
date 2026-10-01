<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre DIAGNOSTICO.
 */
class DAODiagnostico
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
     * Recupera los diagnósticos registrados, opcionalmente filtrados por ticket.
     *
     * @param string|null $idTicket Ticket por el cual filtrar, o null para traer todos.
     * @return array Arreglo con los datos de cada diagnóstico.
     */
    public function listar(?string $idTicket = null): array
    {
        $sql = "
            SELECT
                d.idDiagnostico,
                d.idTicket,
                t.equipo,
                d.cedulaTecnico,
                d.diagnostico,
                d.fechaDiagnostico
            FROM DIAGNOSTICO AS d
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
        ";

        if ($idTicket !== null) {
            $sql .= " WHERE d.idTicket = :idTicket ";
        }

        $sql .= " ORDER BY d.fechaDiagnostico DESC ";

        $consulta = $this->conexion->prepare($sql);

        if ($idTicket !== null) {
            $consulta->execute([":idTicket" => $idTicket]);
        } else {
            $consulta->execute();
        }

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un diagnóstico puntual a partir de su id.
     *
     * @param int $idDiagnostico Id del diagnóstico.
     * @return array|null Los datos del diagnóstico, o null si no existe.
     */
    public function obtener(int $idDiagnostico): ?array
    {
        $sql = "
            SELECT
                d.idDiagnostico,
                d.idTicket,
                t.equipo,
                d.cedulaTecnico,
                d.diagnostico,
                d.fechaDiagnostico
            FROM DIAGNOSTICO AS d
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
            WHERE d.idDiagnostico = :idDiagnostico
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idDiagnostico" => $idDiagnostico]);

        $diagnostico = $consulta->fetch(PDO::FETCH_ASSOC);

        return $diagnostico === false ? null : $diagnostico;
    }

    /**
     * Registra un nuevo diagnóstico.
     *
     * @param array $datos Arreglo con las claves idTicket, cedulaTecnico y diagnostico.
     * @return int El idDiagnostico generado.
     */
    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO DIAGNOSTICO (idTicket, cedulaTecnico, diagnostico)
            VALUES (:idTicket, :cedulaTecnico, :diagnostico)
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idTicket" => $datos["idTicket"],
            ":cedulaTecnico" => $datos["cedulaTecnico"],
            ":diagnostico" => $datos["diagnostico"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Actualiza el texto de un diagnóstico existente.
     *
     * @param int $idDiagnostico Id del diagnóstico a actualizar.
     * @param array $datos Arreglo con la clave diagnostico.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(int $idDiagnostico, array $datos): bool
    {
        $sql = "
            UPDATE DIAGNOSTICO
            SET diagnostico = :diagnostico
            WHERE idDiagnostico = :idDiagnostico
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":diagnostico" => $datos["diagnostico"],
            ":idDiagnostico" => $idDiagnostico
        ]);
    }

    /**
     * Elimina un diagnóstico de la base de datos.
     *
     * @param int $idDiagnostico Id del diagnóstico a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(int $idDiagnostico): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM DIAGNOSTICO WHERE idDiagnostico = :idDiagnostico");

        return $consulta->execute([":idDiagnostico" => $idDiagnostico]);
    }

    /**
     * Verifica si un ticket existe, para no registrar diagnósticos sueltos.
     *
     * @param string $idTicket Id del ticket.
     * @return bool true si el ticket existe.
     */
    public function existeTicket(string $idTicket): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM TICKET WHERE idTicket = :idTicket");
        $consulta->execute([":idTicket" => $idTicket]);

        return ((int) $consulta->fetchColumn()) > 0;
    }
}

?>
