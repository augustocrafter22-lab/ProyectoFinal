<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre EQUIPO: listar, obtener, crear, actualizar y eliminar.
 */
class DAOEquipo
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
     * Recupera todos los equipos junto con el laboratorio al que pertenecen.
     *
     * @return array Arreglo asociativo con los datos de cada equipo.
     */
    public function listar(array $filtros = []): array
    {
        $condiciones = [];
        $parametros = [];

        if (($filtros["estado"] ?? "") !== "") {
            $condiciones[] = "e.estado = :estado";
            $parametros[":estado"] = $filtros["estado"];
        }

        if (($filtros["laboratorio"] ?? "") !== "") {
            $condiciones[] = "(e.idLaboratorio LIKE :idLaboratorio OR l.numeroLaboratorio LIKE :numeroLaboratorio)";
            $busquedaLaboratorio = "%" . $filtros["laboratorio"] . "%";
            $parametros[":idLaboratorio"] = $busquedaLaboratorio;
            $parametros[":numeroLaboratorio"] = $busquedaLaboratorio;
        }

        if (($filtros["disponibilidad"] ?? "") !== "") {
            $condiciones[] = "e.disponibilidad = :disponibilidad";
            $parametros[":disponibilidad"] = $filtros["disponibilidad"];
        }

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
        ";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY e.idEquipo ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un equipo puntual a partir de su id.
     *
     * @param string $idEquipo Id del equipo.
     * @return array|null Los datos del equipo, o null si no existe.
     */
    public function obtener(string $idEquipo): ?array
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
     * Registra un nuevo equipo.
     *
     * @param array $datos Arreglo con las claves idEquipo, idLaboratorio, marca,
     *                     estado, disponibilidad e informacion.
     * @return bool true si se ejecutó correctamente.
     */
    public function crear(array $datos): bool
    {
        $sql = "
            INSERT INTO EQUIPO (idEquipo, idLaboratorio, marca, estado, disponibilidad, informacion)
            VALUES (:idEquipo, :idLaboratorio, :marca, :estado, :disponibilidad, :informacion)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idEquipo" => $datos["idEquipo"],
            ":idLaboratorio" => $datos["idLaboratorio"],
            ":marca" => $datos["marca"],
            ":estado" => $datos["estado"],
            ":disponibilidad" => $datos["disponibilidad"],
            ":informacion" => $datos["informacion"]
        ]);
    }

    /**
     * Actualiza los datos de un equipo existente.
     *
     * @param string $idEquipo Id del equipo a actualizar.
     * @param array $datos Arreglo con las claves idLaboratorio, marca, estado,
     *                     disponibilidad e informacion.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(string $idEquipo, array $datos, string $cedulaUsuario): bool
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

        $this->conexion->beginTransaction();

        try {
            $consultaAnterior = $this->conexion->prepare(
                "SELECT idLaboratorio, estado FROM EQUIPO WHERE idEquipo = :idEquipo FOR UPDATE"
            );
            $consultaAnterior->execute([":idEquipo" => $idEquipo]);
            $datosAnteriores = $consultaAnterior->fetch(PDO::FETCH_ASSOC);

            if ($datosAnteriores === false) {
                throw new RuntimeException("El equipo ya no existe.");
            }

            $consulta = $this->conexion->prepare($sql);
            $actualizado = $consulta->execute([
                ":idEquipo" => $idEquipo,
                ":idLaboratorio" => $datos["idLaboratorio"],
                ":marca" => $datos["marca"],
                ":estado" => $datos["estado"],
                ":disponibilidad" => $datos["disponibilidad"],
                ":informacion" => $datos["informacion"]
            ]);

            if ($datosAnteriores["idLaboratorio"] !== $datos["idLaboratorio"]) {
                $registroMovimiento = $this->conexion->prepare(
                    "INSERT INTO MOVIMIENTO_EQUIPO
                        (idEquipo, idLaboratorioAnterior, idLaboratorioNuevo, cedulaUsuario)
                     VALUES (:idEquipo, :anterior, :nuevo, :cedulaUsuario)"
                );
                $registroMovimiento->execute([
                    ":idEquipo" => $idEquipo,
                    ":anterior" => $datosAnteriores["idLaboratorio"],
                    ":nuevo" => $datos["idLaboratorio"],
                    ":cedulaUsuario" => $cedulaUsuario
                ]);
            }

            if ($datosAnteriores["estado"] !== $datos["estado"]) {
                $registroEstado = $this->conexion->prepare(
                    "INSERT INTO HISTORIAL_ESTADO_EQUIPO
                        (idEquipo, estadoAnterior, estadoNuevo, cedulaUsuario)
                     VALUES (:idEquipo, :anterior, :nuevo, :cedulaUsuario)"
                );
                $registroEstado->execute([
                    ":idEquipo" => $idEquipo,
                    ":anterior" => $datosAnteriores["estado"],
                    ":nuevo" => $datos["estado"],
                    ":cedulaUsuario" => $cedulaUsuario
                ]);
            }

            $this->conexion->commit();

            return $actualizado;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Elimina un equipo de la base de datos.
     *
     * @param string $idEquipo Id del equipo a eliminar.
     * @return bool true si eliminarlo se ejecutó correctamente.
     */
    public function eliminar(string $idEquipo): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM EQUIPO WHERE idEquipo = :idEquipo");

        return $consulta->execute([":idEquipo" => $idEquipo]);
    }

    /**
     * Verifica si un laboratorio existe, para no insertar equipos sueltos.
     *
     * @param string $idLaboratorio Id del laboratorio.
     * @return bool true si el laboratorio existe.
     */
    public function existeLaboratorio(string $idLaboratorio): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM LABORATORIO WHERE idLaboratorio = :idLaboratorio");
        $consulta->execute([":idLaboratorio" => $idLaboratorio]);

        return ((int) $consulta->fetchColumn()) > 0;
    }
}

?>