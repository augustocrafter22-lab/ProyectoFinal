<?php

class AccesoDatosSolicitudLaboratorio
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerLaboratorios(): array
    {
        $sql = "SELECT idLaboratorio, numeroLaboratorio, estado FROM LABORATORIO ORDER BY numeroLaboratorio ASC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearSolicitud(
        string $idLaboratorio,
        string $cedulaSolicitante,
        bool $solicitaSoftware,
        ?string $detalle,
        ?string $restricciones,
        string $fechaEstimada,
        string $horaEstimada
    ): bool {
        $sql = "
            INSERT INTO SOLICITUD_LABORATORIO
                (idLaboratorio, cedulaSolicitante, solicitaSoftware, detalle, restricciones, fechaEstimada, horaEstimada)
            VALUES
                (:idLaboratorio, :cedulaSolicitante, :solicitaSoftware, :detalle, :restricciones, :fechaEstimada, :horaEstimada)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idLaboratorio" => $idLaboratorio,
            ":cedulaSolicitante" => $cedulaSolicitante,
            ":solicitaSoftware" => (int) $solicitaSoftware,
            ":detalle" => $detalle,
            ":restricciones" => $restricciones,
            ":fechaEstimada" => $fechaEstimada,
            ":horaEstimada" => $horaEstimada
        ]);
    }

    public function obtenerSolicitudes(): array
    {
        $sql = "
            SELECT
                s.idSolicitud,
                s.idLaboratorio,
                l.numeroLaboratorio,
                s.cedulaSolicitante,
                s.solicitaSoftware,
                s.detalle,
                s.restricciones,
                s.fechaEstimada,
                s.horaEstimada,
                s.fechaCreacion
            FROM SOLICITUD_LABORATORIO AS s
            INNER JOIN LABORATORIO AS l ON l.idLaboratorio = s.idLaboratorio
            ORDER BY s.fechaEstimada ASC, s.horaEstimada ASC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>