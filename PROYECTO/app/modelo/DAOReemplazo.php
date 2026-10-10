<?php

/**
 * Acceso a datos para reemplazo.
 */
class DAOReemplazo
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function crear(array $datos): int
    {
        $consulta = $this->conexion->prepare(
            "INSERT INTO REEMPLAZO (idEquipo, componente, descripcion, cedulaTecnico)
             VALUES (:idEquipo, :componente, :descripcion, :cedulaTecnico)"
        );
        $consulta->execute([
            ":idEquipo" => $datos["idEquipo"],
            ":componente" => $datos["componente"],
            ":descripcion" => $datos["descripcion"],
            ":cedulaTecnico" => $datos["cedulaTecnico"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function obtener(int $idReemplazo): ?array
    {
        $consulta = $this->conexion->prepare(
            "SELECT idReemplazo, idEquipo, componente, descripcion, cedulaTecnico, fechaReemplazo
             FROM REEMPLAZO
             WHERE idReemplazo = :idReemplazo"
        );
        $consulta->execute([":idReemplazo" => $idReemplazo]);
        $reemplazo = $consulta->fetch(PDO::FETCH_ASSOC);

        return $reemplazo === false ? null : $reemplazo;
    }
}

?>