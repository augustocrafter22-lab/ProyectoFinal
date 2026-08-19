<?php

/**
 * Clase que gestiona los diagnósticos registrados en la base de datos
 * y su asociación con los tickets correspondientes.
 */
class AccesoDatosDiagnostico {
    private PDO $conexion;

    /**
     * @param PDO $conexion La conexión a la base de datos.
     */
    public function __construct(PDO $conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Registra un diagnóstico y lo asocia al ticket correspondiente.
     *
     * @param string $idTicket El ticket que originó la revisión.
     * @param string $cedulaTecnico El técnico que realiza el diagnóstico.
     * @param string $diagnostico El texto del diagnóstico técnico.
     * @return bool true si se registró correctamente.
     */
    public function registrarDiagnostico(string $idTicket, string $cedulaTecnico, string $diagnostico): bool {
        $sql = "
            INSERT INTO DIAGNOSTICO (idTicket, cedulaTecnico, diagnostico)
            VALUES (:idTicket, :cedulaTecnico, :diagnostico)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idTicket" => $idTicket,
            ":cedulaTecnico" => $cedulaTecnico,
            ":diagnostico" => $diagnostico
        ]);
    }

    /**
     * Recupera los diagnósticos registrados, permitiendo filtrarlos
     * opcionalmente por ticket. Si no se proporciona un ticket, se recuperan todos los diagnósticos.
     *
     * @param ?string $idTicket El ticket por el cual filtrar, o null para traer todos.
     * @return array Un arreglo con los datos de cada diagnóstico.
     */
    public function listarDiagnosticos(?string $idTicket = null): array {
        $sql = "
            SELECT
                d.idDiagnostico,
                d.idTicket,
                d.cedulaTecnico,
                d.diagnostico,
                d.fechaDiagnostico
            FROM DIAGNOSTICO AS d
        ";

        if ($idTicket !== null && $idTicket !== "") {
            $sql .= " WHERE d.idTicket = :idTicket ";
        }

        $sql .= " ORDER BY d.fechaDiagnostico DESC ";

        $consulta = $this->conexion->prepare($sql);

        if ($idTicket !== null && $idTicket !== "") {
            $consulta->execute([":idTicket" => $idTicket]);
        } else {
            $consulta->execute();
        }

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un diagnóstico junto con el ticket asociado.
     *
     * @param int $idDiagnostico El identificador del diagnóstico.
     * @return array|null Los datos del diagnóstico, o null si no existe.
     */
    public function obtenerDiagnostico(int $idDiagnostico): ?array {
        $sql = "
            SELECT
                d.idDiagnostico,
                d.idTicket,
                d.cedulaTecnico,
                d.diagnostico,
                d.fechaDiagnostico
            FROM DIAGNOSTICO AS d
            WHERE d.idDiagnostico = :idDiagnostico
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idDiagnostico" => $idDiagnostico]);

        $diagnostico = $consulta->fetch(PDO::FETCH_ASSOC);

        return $diagnostico === false ? null : $diagnostico;
    }
}

?>