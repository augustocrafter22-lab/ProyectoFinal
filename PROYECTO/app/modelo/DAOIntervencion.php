<?php

/**
 * Acceso a datos para intervencion
 */
class DAOIntervencion
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function crear(array $datos): int
    {
        $consulta = $this->conexion->prepare(
            "INSERT INTO INTERVENCION (idEquipo, tipo, descripcion, cedulaTecnico)
             VALUES (:idEquipo, :tipo, :descripcion, :cedulaTecnico)"
        );
        $consulta->execute([
            ":idEquipo" => $datos["idEquipo"],
            ":tipo" => $datos["tipo"],
            ":descripcion" => $datos["descripcion"],
            ":cedulaTecnico" => $datos["cedulaTecnico"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function obtener(int $idIntervencion): ?array
    {
        $consulta = $this->conexion->prepare(
            "SELECT idIntervencion, idEquipo, tipo, descripcion, cedulaTecnico, fechaIntervencion
             FROM INTERVENCION
             WHERE idIntervencion = :idIntervencion"
        );
        $consulta->execute([":idIntervencion" => $idIntervencion]);
        $intervencion = $consulta->fetch(PDO::FETCH_ASSOC);

        return $intervencion === false ? null : $intervencion;
    }
}

?>