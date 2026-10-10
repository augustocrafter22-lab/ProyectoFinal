<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOHistorialEquipo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/RegistradorErrores.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Consulta la trazabilidad técnica completa de un equipo.
 */
class ControladorHistorialEquipo
{
    private ConectorPDO $conectorPDO;
    private DAOHistorialEquipo $dao;

    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];
        Sesion::verificarRolApi(["coordinador", "tecnico"]);

        try {
            $this->conectar();

            if ($metodo !== "GET") {
                throw new Exception("Método no permitido.", 405);
            }

            $resultado = $this->listar();
            $this->conectorPDO->desconectar();
            RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);
        } catch (PDOException $e) {
            RegistradorErrores::registrar($e);
            RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
        } catch (Exception $e) {
            $codigo = $e->getCode() >= 400 && $e->getCode() <= 599 ? (int) $e->getCode() : 500;

            if ($codigo === 500) {
                RegistradorErrores::registrar($e);
                RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
            } else {
                RespuestaJson::error($e->getMessage(), $codigo);
            }
        }
    }

    private function conectar(): void
    {
        $this->conectorPDO = new ConectorPDO($_ENV["BD_HOST"], $_ENV["BD_USER"], $_ENV["BD_PASS"], $_ENV["BD_NAME"]);
        $conexion = $this->conectorPDO->establecerConexion();

        if ($conexion === null) {
            throw new Exception("No se pudo conectar a la base de datos.", 500);
        }

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