<?php

/**
 * Clase que gestiona las soluciones registradas en la base de datos
 * y su vínculo con el diagnóstico.
 */
class AccesoDatosSolucion {
    private PDO $conexion;

    /**
     * @param PDO $conexion La conexión a la base de datos.
     */
    public function __construct(PDO $conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Registra una solución asociada a un diagnóstico ya existente.
     *
     * @param int $idDiagnostico El diagnóstico que se está resolviendo.
     * @param string $cedulaTecnico El técnico que aplica la solución.
     * @param string $solucion El texto de la solución técnica aplicada.
     * @return bool true si se registró correctamente.
     */
    public function registrarSolucion(int $idDiagnostico, string $cedulaTecnico, string $solucion): bool {
        $sql = "
            INSERT INTO SOLUCION (idDiagnostico, cedulaTecnico, solucion)
            VALUES (:idDiagnostico, :cedulaTecnico, :solucion)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idDiagnostico" => $idDiagnostico,
            ":cedulaTecnico" => $cedulaTecnico,
            ":solucion" => $solucion
        ]);
    }

    /**
     * Recupera las soluciones registradas para un diagnóstico especifico.
     *
     * @param int $idDiagnostico El diagnóstico del cual se buscan soluciones.
     * @return array Un arreglo con los datos de cada solución.
     */
    public function listarSolucionesPorDiagnostico(int $idDiagnostico): array {
        $sql = "
            SELECT
                s.idSolucion,
                s.idDiagnostico,
                s.cedulaTecnico,
                s.solucion,
                s.fechaSolucion
            FROM SOLUCION AS s
            WHERE s.idDiagnostico = :idDiagnostico
            ORDER BY s.fechaSolucion DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idDiagnostico" => $idDiagnostico]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>