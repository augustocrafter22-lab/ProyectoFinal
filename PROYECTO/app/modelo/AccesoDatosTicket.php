<?php

/**
 * Clase que recupera los tickets registrados en la base de datos.
 */
class AccesoDatosTicket {
    private PDO $conexion;

    /**
     * 
     * @param PDO $conexion La conexion a la base de datos. 
     */
    public function __construct(PDO $conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Recuperalos tickets registrados.
     * @return array Un arreglo con los datos de cada ticket.
     */
    public function listarTickets(): array {
        $sql = "
            SELECT
                idTicket,
                laboratorio,
                equipo,
                asunto,
                descripcion,
                turno,
                grupo,
                profesor,
                estado,
                prioridad,
                fechaCreacion,
                fechaFinalizacion
            FROM TICKET
            ORDER BY fechaCreacion DESC
        ";

        $consulta = $this->conexion->query($sql);

        $tickets = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $tickets;
    }

    /**
     * Registra un nuevo ticket reportado por un docente.
     *
     * @return string El idTicket generado para el nuevo registro.
     */
    public function registrarTicket(string $laboratorio, string $equipo, string $asunto,
        string $descripcion, string $turno, string $grupo, string $profesor): string {

        $idTicket = $this->generarIdTicket();

        $sql = "
            INSERT INTO TICKET (idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor)
            VALUES (:idTicket, :laboratorio, :equipo, :asunto, :descripcion, :turno, :grupo, :profesor)
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":idTicket" => $idTicket,
            ":laboratorio" => $laboratorio,
            ":equipo" => $equipo,
            ":asunto" => $asunto,
            ":descripcion" => $descripcion,
            ":turno" => $turno,
            ":grupo" => $grupo,
            ":profesor" => $profesor
        ]);

        return $idTicket;
    }

    /**
     * Actualiza el estado y la prioridad de un ticket existente.
     * Si el estado pasa a "Resuelto" o "Cerrado" se registra la fecha de finalización.
     *
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizarEstadoYPrioridad(string $idTicket, string $estado, string $prioridad): bool {
        $estadosFinalizados = ["Resuelto", "Cerrado"];
        $fechaFinalizacion = in_array($estado, $estadosFinalizados, true) ? date("Y-m-d H:i:s") : null;

        $sql = "
            UPDATE TICKET
            SET
                estado = :estado,
                prioridad = :prioridad,
                fechaFinalizacion = :fechaFinalizacion
            WHERE idTicket = :idTicket
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":estado" => $estado,
            ":prioridad" => $prioridad,
            ":fechaFinalizacion" => $fechaFinalizacion,
            ":idTicket" => $idTicket
        ]);
    }

    /**
     * Genera un idTicket único con el formato INC-{año}-{secuencia}.
     */
    private function generarIdTicket(): string {
        $anio = date("Y");

        $sql = "SELECT COUNT(*) FROM TICKET WHERE idTicket LIKE :patron";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":patron" => "INC-$anio-%"]);

        $cantidad = (int) $consulta->fetchColumn();

        return "INC-$anio-" . str_pad((string) ($cantidad + 1), 4, "0", STR_PAD_LEFT);
    }
}

?>