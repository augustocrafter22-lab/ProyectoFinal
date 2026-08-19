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
}

?>