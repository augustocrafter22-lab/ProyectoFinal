<?php

class AccesoDatosDashboard
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

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

    public function contarTotal(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM TICKET";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return (int) $consulta->fetch(PDO::FETCH_ASSOC)["total"];
    }

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