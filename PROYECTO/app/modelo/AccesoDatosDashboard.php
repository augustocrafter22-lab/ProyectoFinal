<?php

class AccesoDatosDashboard
{
    private PDO $conexion;

    /**
     * Inicializa el acceso a datos con la conexión PDO proporcionada.
     *
     * @param PDO $conexion Conexión activa a la base de datos.
     * @return void
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Cuenta la cantidad de tickets agrupados por su estado.
     *
     * @return array Arreglo asociativo con la cantidad de tickets por cada estado (Pendiente, En Proceso, Resuelto, Cerrado).
     */
    public function contarPorEstado(): array
    {
        $sql = "
            SELECT estado, COUNT(*) AS cantidad
            FROM TICKET
            GROUP BY estado
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        // Inicializamos todos los estados en 0 por si alguno no tiene tickets todavía
        $conteo = [
            "Pendiente" => 0,
            "En Proceso" => 0,
            "Resuelto" => 0,
            "Cerrado" => 0
        ];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $conteo[$fila["estado"]] = (int) $fila["cantidad"];
        }

        return $conteo;
    }

    /**
     * Obtiene la cantidad total de tickets registrados.
     *
     * @return int Número total de tickets.
     */
    public function contarTotal(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM TICKET";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return (int) $consulta->fetch(PDO::FETCH_ASSOC)["total"];
    }

    /**
     * Obtiene los tiempos de resolución (en días) de los tickets finalizados.
     *
     * @return array Lista de tickets finalizados con su id y la cantidad de días entre creación y finalización, ordenada por fecha de finalización descendente.
     */
    public function obtenerTiemposResolucion(): array
    {
        $sql = "
            SELECT
                idTicket,
                DATEDIFF(fechaFinalizacion, fechaCreacion) AS dias
            FROM TICKET
            WHERE fechaFinalizacion IS NOT NULL
            ORDER BY fechaFinalizacion DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la cantidad de incidencias (tickets) agrupadas por laboratorio.
     *
     * @return array Lista de laboratorios con la cantidad de tickets asociados, ordenada de mayor a menor cantidad.
     */
    public function obtenerIncidenciasPorSalon(): array
    {
        $sql = "
            SELECT laboratorio, COUNT(*) AS cantidad
            FROM TICKET
            GROUP BY laboratorio
            ORDER BY cantidad DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>