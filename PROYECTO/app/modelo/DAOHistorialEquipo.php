<?php

/**
 * Acceso a los eventos que componen la trazabilidad técnica de un equipo.
 */
class DAOHistorialEquipo
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Devuelve el historial del equipo en un formato comum y cronologico.
     */
    public function listar(string $idEquipo): array
    {
        $historial = [];
        $consultas = [
            [
                "SELECT 'Cambio de estado' AS tipo,
                        CONCAT(estadoAnterior, ' -> ', estadoNuevo) AS detalle,
                        fechaCambio AS fecha,
                        cedulaUsuario AS responsable
                 FROM HISTORIAL_ESTADO_EQUIPO
                 WHERE idEquipo = :idEquipo",
                $idEquipo
            ],
            [
                "SELECT 'Cambio de ubicación' AS tipo,
                        CONCAT(labAnterior.numeroLaboratorio, ' -> ', labNuevo.numeroLaboratorio) AS detalle,
                        movimiento.fechaMovimiento AS fecha,
                        movimiento.cedulaUsuario AS responsable
                 FROM MOVIMIENTO_EQUIPO AS movimiento
                 INNER JOIN LABORATORIO AS labAnterior
                    ON labAnterior.idLaboratorio = movimiento.idLaboratorioAnterior
                 INNER JOIN LABORATORIO AS labNuevo
                    ON labNuevo.idLaboratorio = movimiento.idLaboratorioNuevo
                 WHERE movimiento.idEquipo = :idEquipo",
                $idEquipo
            ],
            [
                "SELECT CONCAT('Intervención: ', tipo) AS tipo,
                        descripcion AS detalle,
                        fechaIntervencion AS fecha,
                        cedulaTecnico AS responsable
                 FROM INTERVENCION
                 WHERE idEquipo = :idEquipo",
                $idEquipo
            ],
            [
                "SELECT 'Reemplazo de componente' AS tipo,
                        CONCAT(componente, ': ', descripcion) AS detalle,
                        fechaReemplazo AS fecha,
                        cedulaTecnico AS responsable
                 FROM REEMPLAZO
                 WHERE idEquipo = :idEquipo",
                $idEquipo
            ],
            [
                "SELECT 'Reparación' AS tipo,
                        CONCAT('Ticket ', idTicket, ': ', reparacion) AS detalle,
                        fechaReparacion AS fecha,
                        cedulaTecnico AS responsable
                 FROM REPARACION
                 WHERE idEquipo = :idEquipo",
                $idEquipo
            ]
        ];

        foreach ($consultas as [$sql, $id]) {
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([":idEquipo" => $id]);
            $historial = array_merge($historial, $consulta->fetchAll(PDO::FETCH_ASSOC));
        }

        usort($historial, static function (array $primero, array $segundo): int {
            return strcmp($segundo["fecha"], $primero["fecha"]);
        });

        return $historial;
    }

    public function existeEquipo(string $idEquipo): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM EQUIPO WHERE idEquipo = :idEquipo");
        $consulta->execute([":idEquipo" => $idEquipo]);

        return (int) $consulta->fetchColumn() > 0;
    }

}

?>