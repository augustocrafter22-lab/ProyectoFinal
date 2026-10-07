<?php

/**
 * DAO Data Access Object que agrupa toda la comunicación con la base
 * de datos sobre SOLICITUD_LABORATORIO: listar, obtener, crear,
 * actualizar y eliminar.
 *
 * Una solicitud puede ser de preparación de un laboratorio
 * (solicitaSoftware = 0) o de instalación de software (solicitaSoftware = 1),
 * ese es el "tipo" por el que se puede filtrar el listado.
 */
class DAOSolicitudLaboratorio
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
     * Recupera las solicitudes registradas, opcionalmente filtradas,
     * ordenadas por fecha y hora estimada.
     *
     * @param array $filtros Claves opcionales: solicitaSoftware (1 o 0),
     *                       idLaboratorio y fechaEstimada.
     * @return array Arreglo con los datos de cada solicitud.
     */
    public function listar(array $filtros = []): array
    {
        $condiciones = [];
        $parametros = [];

        if (isset($filtros["solicitaSoftware"])) {
            $condiciones[] = "s.solicitaSoftware = :solicitaSoftware";
            $parametros[":solicitaSoftware"] = $filtros["solicitaSoftware"];
        }

        if (($filtros["idLaboratorio"] ?? "") !== "") {
            $condiciones[] = "s.idLaboratorio = :idLaboratorio";
            $parametros[":idLaboratorio"] = $filtros["idLaboratorio"];
        }

        if (($filtros["fechaEstimada"] ?? "") !== "") {
            $condiciones[] = "s.fechaEstimada = :fechaEstimada";
            $parametros[":fechaEstimada"] = $filtros["fechaEstimada"];
        }

        $sql = $this->consultaBase();

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY s.fechaEstimada ASC, s.horaEstimada ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        $solicitudes = [];

        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $solicitudes[] = $this->armarSolicitud($fila);
        }

        return $solicitudes;
    }

    /**
     * Recupera una solicitud puntual a partir de su id.
     *
     * @param int $idSolicitud Id de la solicitud.
     * @return array|null Los datos de la solicitud, o null si no existe.
     */
    public function obtener(int $idSolicitud): ?array
    {
        $consulta = $this->conexion->prepare($this->consultaBase() . " WHERE s.idSolicitud = :idSolicitud");
        $consulta->execute([":idSolicitud" => $idSolicitud]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila === false ? null : $this->armarSolicitud($fila);
    }

    /**
     * Registra una solicitud nueva.
     *
     * @param array $datos Arreglo con las claves idLaboratorio, cedulaSolicitante,
     *                     solicitaSoftware (1 o 0), detalle, restricciones,
     *                     fechaEstimada y horaEstimada.
     * @return int El idSolicitud generado para el nuevo registro.
     */
    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO SOLICITUD_LABORATORIO
                (idLaboratorio, cedulaSolicitante, solicitaSoftware, detalle, restricciones, fechaEstimada, horaEstimada)
            VALUES
                (:idLaboratorio, :cedulaSolicitante, :solicitaSoftware, :detalle, :restricciones, :fechaEstimada, :horaEstimada)
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idLaboratorio" => $datos["idLaboratorio"],
            ":cedulaSolicitante" => $datos["cedulaSolicitante"],
            ":solicitaSoftware" => $datos["solicitaSoftware"],
            ":detalle" => $datos["detalle"],
            ":restricciones" => $datos["restricciones"],
            ":fechaEstimada" => $datos["fechaEstimada"],
            ":horaEstimada" => $datos["horaEstimada"]
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Modifica los datos de una solicitud existente. El solicitante y la
     * fecha de creación no se pueden cambiar.
     *
     * @param int $idSolicitud Id de la solicitud a modificar.
     * @param array $datos Arreglo con las claves idLaboratorio, solicitaSoftware (1 o 0),
     *                     detalle, restricciones, fechaEstimada y horaEstimada.
     * @return bool true si la actualización se ejecutó correctamente.
     */
    public function actualizar(int $idSolicitud, array $datos): bool
    {
        $sql = "
            UPDATE SOLICITUD_LABORATORIO
            SET idLaboratorio = :idLaboratorio,
                solicitaSoftware = :solicitaSoftware,
                detalle = :detalle,
                restricciones = :restricciones,
                fechaEstimada = :fechaEstimada,
                horaEstimada = :horaEstimada
            WHERE idSolicitud = :idSolicitud
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idLaboratorio" => $datos["idLaboratorio"],
            ":solicitaSoftware" => $datos["solicitaSoftware"],
            ":detalle" => $datos["detalle"],
            ":restricciones" => $datos["restricciones"],
            ":fechaEstimada" => $datos["fechaEstimada"],
            ":horaEstimada" => $datos["horaEstimada"],
            ":idSolicitud" => $idSolicitud
        ]);
    }

    /**
     * Elimina una solicitud de la base de datos.
     *
     * @param int $idSolicitud Id de la solicitud a eliminar.
     * @return bool true si la eliminación se ejecutó correctamente.
     */
    public function eliminar(int $idSolicitud): bool
    {
        $consulta = $this->conexion->prepare("DELETE FROM SOLICITUD_LABORATORIO WHERE idSolicitud = :idSolicitud");

        return $consulta->execute([":idSolicitud" => $idSolicitud]);
    }

    /**
     * Verifica si existe un laboratorio con el id indicado.
     *
     * @param string $idLaboratorio Id del laboratorio.
     * @return bool true si el laboratorio existe, false en caso contrario.
     */
    public function existeLaboratorio(string $idLaboratorio): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM LABORATORIO WHERE idLaboratorio = :idLaboratorio");
        $consulta->execute([":idLaboratorio" => $idLaboratorio]);

        return ((int) $consulta->fetchColumn()) > 0;
    }

    /**
     * Consulta común de listar y obtener: la solicitud junto con el número
     * de su laboratorio.
     *
     * @return string Sentencia SQL sin WHERE ni ORDER BY.
     */
    private function consultaBase(): string
    {
        return "
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
        ";
    }

    /**
     * Convierte una fila de la consulta en la solicitud que se devuelve a la API:
     * solicitaSoftware pasa a booleano.
     *
     * @param array $fila Fila obtenida con consultaBase().
     * @return array Los datos de la solicitud.
     */
    private function armarSolicitud(array $fila): array
    {
        $fila["solicitaSoftware"] = (bool) $fila["solicitaSoftware"];

        return $fila;
    }
}

?>
