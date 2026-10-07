<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre LABORATORIO: listar, obtener, crear, actualizar y eliminar.
 */
class DAOLaboratorio
{
    private PDO $conexion;

    /**
     * Inicializa el DAO con una conexión activa a la base de datos.
     *
     * @param PDO $conexion Conexión PDO ya establecida.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Recupera todos los laboratorios ordenados por su número.
     *
     * @return array Arreglo con idLaboratorio, numeroLaboratorio y estado de cada laboratorio.
     */
    public function listar(): array
    {
        $consulta = $this->conexion->prepare("
            SELECT idLaboratorio, numeroLaboratorio, estado
            FROM LABORATORIO
            ORDER BY numeroLaboratorio ASC
        ");
        $consulta->execute();

        $laboratorios = [];

        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $laboratorios[] = $this->armarLaboratorio($fila);
        }

        return $laboratorios;
    }

    /**
     * Recupera un laboratorio puntual a partir de su id.
     *
     * @param string $idLaboratorio Id del laboratorio.
     * @return array|null Los datos del laboratorio, o null si no existe.
     */
    public function obtener(string $idLaboratorio): ?array
    {
        $consulta = $this->conexion->prepare("
            SELECT idLaboratorio, numeroLaboratorio, estado
            FROM LABORATORIO
            WHERE idLaboratorio = :idLaboratorio
        ");
        $consulta->execute([":idLaboratorio" => $idLaboratorio]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila === false ? null : $this->armarLaboratorio($fila);
    }

    /**
     * Registra un laboratorio nuevo.
     *
     * @param array $datos Arreglo con las claves idLaboratorio,
     *                     numeroLaboratorio y estado (1 o 0).
     * @return bool true si la inserción se ejecutó correctamente.
     */
    public function crear(array $datos): bool
    {
        $sql = "
            INSERT INTO LABORATORIO (idLaboratorio, numeroLaboratorio, estado)
            VALUES (:idLaboratorio, :numeroLaboratorio, :estado)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idLaboratorio" => $datos["idLaboratorio"],
            ":numeroLaboratorio" => $datos["numeroLaboratorio"],
            ":estado" => $datos["estado"]
        ]);
    }

    /**
     * Modifica el número y el estado de un laboratorio existente.
     *
     * @param string $idLaboratorio Id del laboratorio a modificar.
     * @param array $datos Arreglo con las claves numeroLaboratorio y estado (1 o 0).
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(string $idLaboratorio, array $datos): bool
    {
        $sql = "
            UPDATE LABORATORIO
            SET numeroLaboratorio = :numeroLaboratorio, estado = :estado
            WHERE idLaboratorio = :idLaboratorio
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":numeroLaboratorio" => $datos["numeroLaboratorio"],
            ":estado" => $datos["estado"],
            ":idLaboratorio" => $idLaboratorio
        ]);
    }

    /**
     * Elimina un laboratorio. Si tiene equipos, tickets o solicitudes
     * relacionados la base lo impide y se lanza una PDOException.
     *
     * @param string $idLaboratorio Id del laboratorio a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(string $idLaboratorio): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM LABORATORIO WHERE idLaboratorio = :idLaboratorio");

        return $consulta->execute([":idLaboratorio" => $idLaboratorio]);
    }

    /**
     * Convierte una fila de la base en el laboratorio que se devuelve a la API:
     * estado pasa a booleano.
     *
     * @param array $fila Fila obtenida de la tabla LABORATORIO.
     * @return array Arreglo con idLaboratorio, numeroLaboratorio y estado.
     */
    private function armarLaboratorio(array $fila): array
    {
        return [
            "idLaboratorio" => $fila["idLaboratorio"],
            "numeroLaboratorio" => $fila["numeroLaboratorio"],
            "estado" => (bool) $fila["estado"]
        ];
    }
}

?>
