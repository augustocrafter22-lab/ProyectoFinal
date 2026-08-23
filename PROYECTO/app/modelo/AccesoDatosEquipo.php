<?php

class AccesoDatosEquipo
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerEquipos(): array
    {
        $sql = "
            SELECT
                e.idEquipo,
                l.numeroLaboratorio AS laboratorio,
                e.marca,
                e.estado,
                e.disponibilidad,
                e.informacion
            FROM EQUIPO AS e
            INNER JOIN LABORATORIO AS l ON l.idLaboratorio = e.idLaboratorio
            ORDER BY e.idEquipo ASC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEquipo(string $idEquipo): ?array
    {
        $sql = "
            SELECT
                e.idEquipo,
                e.idLaboratorio,
                l.numeroLaboratorio AS laboratorio,
                e.marca,
                e.estado,
                e.disponibilidad,
                e.informacion
            FROM EQUIPO AS e
            INNER JOIN LABORATORIO AS l ON l.idLaboratorio = e.idLaboratorio
            WHERE e.idEquipo = :idEquipo
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idEquipo" => $idEquipo]);
        $equipo = $consulta->fetch(PDO::FETCH_ASSOC);

        return $equipo === false ? null : $equipo;
    }

    public function obtenerLaboratorios(): array
    {
        $sql = "SELECT idLaboratorio, numeroLaboratorio FROM LABORATORIO ORDER BY numeroLaboratorio ASC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearEquipo(string $idEquipo, string $idLaboratorio, string $marca, string $estado, string $disponibilidad, string $informacion): bool
    {
        $sql = "
            INSERT INTO EQUIPO (idEquipo, idLaboratorio, marca, estado, disponibilidad, informacion)
            VALUES (:idEquipo, :idLaboratorio, :marca, :estado, :disponibilidad, :informacion)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idEquipo" => $idEquipo,
            ":idLaboratorio" => $idLaboratorio,
            ":marca" => $marca,
            ":estado" => $estado,
            ":disponibilidad" => $disponibilidad,
            ":informacion" => $informacion
        ]);
    }

    public function actualizarEquipo(string $idEquipo, string $idLaboratorio, string $marca, string $estado, string $disponibilidad, string $informacion): bool
    {
        $sql = "
            UPDATE EQUIPO
            SET idLaboratorio = :idLaboratorio,
                marca = :marca,
                estado = :estado,
                disponibilidad = :disponibilidad,
                informacion = :informacion
            WHERE idEquipo = :idEquipo
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idEquipo" => $idEquipo,
            ":idLaboratorio" => $idLaboratorio,
            ":marca" => $marca,
            ":estado" => $estado,
            ":disponibilidad" => $disponibilidad,
            ":informacion" => $informacion
        ]);
    }

    public function eliminarEquipo(string $idEquipo): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM EQUIPO WHERE idEquipo = :idEquipo");

        return $consulta->execute([":idEquipo" => $idEquipo]);
    }
}
?>