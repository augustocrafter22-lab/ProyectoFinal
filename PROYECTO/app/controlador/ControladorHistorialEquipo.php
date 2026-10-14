<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_MODELO . "/DAOHistorialEquipo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Consulta la trazabilidad técnica completa de un equipo.
 */
class ControladorHistorialEquipo
{
    private DAOHistorialEquipo $dao;

    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];
        Sesion::verificarRolApi(["coordinador", "tecnico"]);

        $this->conectar();

        if ($metodo !== "GET") {
            throw new Exception("Método no permitido.", 405);
        }

        $resultado = $this->listar();
        Conexion::desconectar();
        RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);
    }

    private function conectar(): void
    {
        $conexion = Conexion::conectar();

        $this->dao = new DAOHistorialEquipo($conexion);
    }

    private function listar(): array
    {
        $idEquipo = Validador::longitud(
            Validador::requerido($_GET["idEquipo"] ?? "", "idEquipo"),
            1,
            10,
            "idEquipo"
        );

        if (!$this->dao->existeEquipo($idEquipo)) {
            throw new Exception("No existe un equipo con el identificador $idEquipo.", 404);
        }

        return [
            "datos" => $this->dao->listar($idEquipo),
            "mensaje" => "Historial técnico del equipo $idEquipo.",
            "codigo" => 200
        ];
    }

}

?>