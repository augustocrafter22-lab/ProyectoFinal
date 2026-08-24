<?php

class AccesoDatosReparacion {
    private PDO $conexion;

    /**
     * Inicializa el acceso a datos con la conexión PDO a utilizar.
     *
     * @param PDO $conexion Conexión activa a la base de datos.
     * @return void
     */
    public function __construct(PDO $conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Lista los diagnósticos disponibles junto con su ticket y equipo asociado.
     *
     * @return array Listado de diagnósticos con idDiagnostico, idTicket, idEquipo y diagnostico.
     */
    public function listarDiagnosticosDisponibles(): array {
        $sql = "
            SELECT
                d.idDiagnostico,
                d.idTicket,
                t.equipo AS idEquipo,
                d.diagnostico
            FROM DIAGNOSTICO AS d
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
            INNER JOIN EQUIPO AS e ON e.idEquipo = t.equipo
            ORDER BY d.fechaDiagnostico DESC
        ";

        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra una reparación a partir de un diagnóstico existente.
     *
     * @param int $idDiagnostico Identificador del diagnóstico sobre el que se registra la reparación.
     * @param string $cedulaTecnico Cédula del técnico que realiza la reparación.
     * @param string $reparacion Descripción de la reparación realizada.
     * @return bool true si la reparación fue registrada correctamente, false en caso contrario.
     */
    public function registrarReparacion(int $idDiagnostico, string $cedulaTecnico, string $reparacion): bool {
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
            INNER JOIN EQUIPO AS e ON e.idEquipo = t.equipo
            WHERE d.idDiagnostico = :idDiagnostico
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":idDiagnostico" => $idDiagnostico,
            ":cedulaTecnico" => $cedulaTecnico,
            ":reparacion" => $reparacion
        ]);

        return $consulta->rowCount() === 1;
    }

    /**
     * Lista las reparaciones registradas, opcionalmente filtradas por equipo.
     *
     * @param string|null $idEquipo Identificador del equipo a filtrar, o null para listar todas.
     * @return array Listado de reparaciones ordenadas por fecha de reparación descendente.
     */
    public function listarReparaciones(?string $idEquipo = null): array {
        $sql = "
            SELECT
                r.idReparacion,
                r.idDiagnostico,
                r.idTicket,
                r.idEquipo,
                r.cedulaTecnico,
                r.reparacion,
                r.fechaReparacion
            FROM REPARACION AS r
        ";

        if ($idEquipo !== null && $idEquipo !== "") {
            $sql .= " WHERE r.idEquipo = :idEquipo ";
        }

        $sql .= " ORDER BY r.fechaReparacion DESC ";
        $consulta = $this->conexion->prepare($sql);

        if ($idEquipo !== null && $idEquipo !== "") {
            $consulta->execute([":idEquipo" => $idEquipo]);
        } else {
            $consulta->execute();
        }

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>