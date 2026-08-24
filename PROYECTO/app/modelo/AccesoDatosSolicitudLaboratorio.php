<?php

class AccesoDatosSolicitudLaboratorio
{
    private PDO $conexion;

    /**
     * Inicializa el acceso a datos con la conexión PDO a utilizar.
     *
     * @param PDO $conexion Conexión activa a la base de datos.
     * @return void
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Obtiene el listado de laboratorios registrados.
     *
     * @return array Lista de laboratorios con idLaboratorio, numeroLaboratorio y estado, ordenados por numeroLaboratorio.
     */
    public function obtenerLaboratorios(): array
    {
        $sql = "SELECT idLaboratorio, numeroLaboratorio, estado FROM LABORATORIO ORDER BY numeroLaboratorio ASC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra una nueva solicitud de uso de laboratorio.
     *
     * @param string $idLaboratorio Identificador del laboratorio solicitado.
     * @param string $cedulaSolicitante Cédula de la persona que realiza la solicitud.
     * @param bool $solicitaSoftware Indica si se solicita instalación o uso de software.
     * @param string|null $detalle Detalle adicional de la solicitud.
     * @param string|null $restricciones Restricciones indicadas para la solicitud.
     * @param string $fechaEstimada Fecha estimada de uso del laboratorio.
     * @param string $horaEstimada Hora estimada de uso del laboratorio.
     * @return bool True si la solicitud se registró correctamente, false en caso contrario.
     */
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

    /**
     * Obtiene el listado de solicitudes de laboratorio con su información asociada.
     *
     * @return array Lista de solicitudes con datos del laboratorio, ordenadas por fecha y hora estimada ascendente.
     */
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