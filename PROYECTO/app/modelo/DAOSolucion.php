<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre SOLUCION.
 */
class DAOSolucion
{
    private PDO $conexion;

    /**
     * @param PDO $conexion Conexión PDO ya establecida.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Recupera todas las soluciones registradas junto con el ticket y el equipo asociados.
     *
     * @return array Arreglo con los datos de cada solución.
     */
    public function listar(): array
    {
        $sql = "
            SELECT
                s.idSolucion,
                s.idDiagnostico,
                d.idTicket,
                t.equipo,
                s.cedulaTecnico,
                s.solucion,
                s.fechaSolucion
            FROM SOLUCION AS s
            INNER JOIN DIAGNOSTICO AS d ON d.idDiagnostico = s.idDiagnostico
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
            ORDER BY s.fechaSolucion DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera una solución puntual a partir de su id.
     *
     * @param int $idSolucion Id de la solución.
     * @return array|null Los datos de la solución, o null si no existe.
     */
    public function obtener(int $idSolucion): ?array
    {
        $sql = "
            SELECT
                s.idSolucion,
                s.idDiagnostico,
                d.idTicket,
                t.equipo,
                s.cedulaTecnico,
                s.solucion,
                s.fechaSolucion
            FROM SOLUCION AS s
            INNER JOIN DIAGNOSTICO AS d ON d.idDiagnostico = s.idDiagnostico
            INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
            WHERE s.idSolucion = :idSolucion
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idSolucion" => $idSolucion]);

        $solucion = $consulta->fetch(PDO::FETCH_ASSOC);

        return $solucion === false ? null : $solucion;
    }

    /**
     * Registra una nueva solución.
     *
     * @param array $datos Arreglo con las claves idDiagnostico, cedulaTecnico y solucion.
     * @return int El idSolucion generado.
     */
    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO SOLUCION (idDiagnostico, cedulaTecnico, solucion)
            VALUES (:idDiagnostico, :cedulaTecnico, :solucion)
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idDiagnostico" => $datos["idDiagnostico"],
            ":cedulaTecnico" => $datos["cedulaTecnico"],
            ":solucion" => $datos["solucion"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Actualiza el texto de una solución existente.
     *
     * @param int $idSolucion Id de la solución a actualizar.
     * @param array $datos Arreglo con la clave solucion.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(int $idSolucion, array $datos): bool
    {
        $sql = "
            UPDATE SOLUCION
            SET solucion = :solucion
            WHERE idSolucion = :idSolucion
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":solucion" => $datos["solucion"],
            ":idSolucion" => $idSolucion
        ]);
    }

    /**
     * Elimina una solución de la base de datos.
     *
     * @param int $idSolucion Id de la solución a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(int $idSolucion): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM SOLUCION WHERE idSolucion = :idSolucion");

        return $consulta->execute([":idSolucion" => $idSolucion]);
    }

    /**
     * Verifica si un diagnóstico existe, para no registrar soluciones sueltas.
     *
     * @param int $idDiagnostico Id del diagnóstico.
     * @return bool true si el diagnóstico existe.
     */
    public function existeDiagnostico(int $idDiagnostico): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM DIAGNOSTICO WHERE idDiagnostico = :idDiagnostico");
        $consulta->execute([":idDiagnostico" => $idDiagnostico]);

        return ((int) $consulta->fetchColumn()) > 0;
    }
}

?>
