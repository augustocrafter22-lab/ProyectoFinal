<?php

class AccesoDatosEquipo
{
    private PDO $conexion;

    /**
     * Crea una nueva instancia de acceso a datos de equipos.
     *
     * @param PDO $conexion Conexión activa a la base de datos.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Obtiene el listado completo de equipos junto con su laboratorio asociado.
     *
     * @return array Lista de equipos con sus datos y laboratorio.
     */
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

    /**
     * Busca un equipo por su identificador, incluyendo su laboratorio asociado.
     *
     * @param string $idEquipo Identificador del equipo a buscar.
     * @return array|null Los datos del equipo si existe, null en caso contrario.
     */
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

    /**
     * Obtiene el listado de laboratorios disponibles.
     *
     * @return array Lista de laboratorios con su identificador y número.
     */
    public function obtenerLaboratorios(): array
    {
        $sql = "SELECT idLaboratorio, numeroLaboratorio FROM LABORATORIO ORDER BY numeroLaboratorio ASC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo equipo en la base de datos.
     *
     * @param string $idEquipo Identificador del nuevo equipo.
     * @param string $idLaboratorio Identificador del laboratorio al que pertenece.
     * @param string $marca Marca del equipo.
     * @param string $estado Estado del equipo.
     * @param string $disponibilidad Disponibilidad del equipo.
     * @param string $informacion Información adicional del equipo.
     * @return bool true si la inserción fue exitosa, false en caso contrario.
     */
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

    /**
     * Actualiza los datos de un equipo existente.
     *
     * @param string $idEquipo Identificador del equipo a actualizar.
     * @param string $idLaboratorio Identificador del laboratorio al que pertenece.
     * @param string $marca Marca del equipo.
     * @param string $estado Estado del equipo.
     * @param string $disponibilidad Disponibilidad del equipo.
     * @param string $informacion Información adicional del equipo.
     * @return bool true si la actualización fue exitosa, false en caso contrario.
     */
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

    /**
     * Elimina un equipo de la base de datos.
     *
     * @param string $idEquipo Identificador del equipo a eliminar.
     * @return bool true si la eliminación fue exitosa, false en caso contrario.
     */
    public function eliminarEquipo(string $idEquipo): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM EQUIPO WHERE idEquipo = :idEquipo");

        return $consulta->execute([":idEquipo" => $idEquipo]);
    }
}
?>