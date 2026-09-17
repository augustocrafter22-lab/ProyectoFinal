<?php

/**
 * DAO Data Access Object unifica toda la comunicación con la base
 * de datos sobre TICKET.
 *
 * Reemplaza y agrupa lo que antes estaba en AccesoDatosTicket como pede ser
 * listar, obener, registrar, actualizar y eliminar.
 */
class DAOTicket
{
    private PDO $conexion;

    /**
     * Inicializa el DAO con una conexión activa a la base de datos.
     *
     * @param PDO $conexion Conexión PDO.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Recupera todos los tickets registrados.
     *
     * @return array Arreglo asociativo con los datos de cada ticket.
     */
    public function listar(): array
    {
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

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un ticket especifico a partir de su id.
     *
     * @param string $idTicket Id del ticket.
     * @return array|null Los datos del ticket, o null si no existe.
     */
    public function obtener(string $idTicket): ?array
    {
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
            WHERE idTicket = :idTicket
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idTicket" => $idTicket]);

        $ticket = $consulta->fetch(PDO::FETCH_ASSOC);

        return $ticket === false ? null : $ticket;
    }

    /**
     * Registra un nuevo ticket.
     *
     * @param array $datos Arreglo con las claves laboratorio, equipo, asunto,
     *                     descripcion, turno, grupo y profesor.
     * @return string El idTicket generado para el nuevo registro.
     */
    public function crear(array $datos): string
    {
        $idTicket = $this->generarIdTicket();

        $sql = "
            INSERT INTO TICKET (idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor)
            VALUES (:idTicket, :laboratorio, :equipo, :asunto, :descripcion, :turno, :grupo, :profesor)
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idTicket" => $idTicket,
            ":laboratorio" => $datos["laboratorio"],
            ":equipo" => $datos["equipo"],
            ":asunto" => $datos["asunto"],
            ":descripcion" => $datos["descripcion"],
            ":turno" => $datos["turno"],
            ":grupo" => $datos["grupo"],
            ":profesor" => $datos["profesor"]
        ]);

        return $idTicket;
    }

    /**
     * Actualiza el estado y la prioridad de un ticket existente.
     * Si el estado pasa a "Resuelto" o "Cerrado" se registra la fecha de finalizado.
     *
     * @param string $idTicket Id del ticket a actualizar.
     * @param array $datos Arreglo con las claves estado y prioridad.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(string $idTicket, array $datos): bool
    {
        $estadosFinalizados = ["Resuelto", "Cerrado"];
        $fechaFinalizacion = in_array($datos["estado"], $estadosFinalizados, true) ? date("Y-m-d H:i:s") : null;

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
            ":estado" => $datos["estado"],
            ":prioridad" => $datos["prioridad"],
            ":fechaFinalizacion" => $fechaFinalizacion,
            ":idTicket" => $idTicket
        ]);
    }

    /**
     * Elimina un ticket de la base de datos.
     *
     * @param string $idTicket Id del ticket a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(string $idTicket): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM TICKET WHERE idTicket = :idTicket");

        return $consulta->execute([":idTicket" => $idTicket]);
    }

    /**
     * Genera un idTicket único.
     *
     * @return string El idTicket generado.
     */
    private function generarIdTicket(): string
    {
        $anio = date("Y");

        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM TICKET WHERE idTicket LIKE :patron");
        $consulta->execute([":patron" => "INC-$anio-%"]);

        $cantidad = (int) $consulta->fetchColumn();

        return "INC-$anio-" . str_pad((string) ($cantidad + 1), 4, "0", STR_PAD_LEFT);
    }
}

?>