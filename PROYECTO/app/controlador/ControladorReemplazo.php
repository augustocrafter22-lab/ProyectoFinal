<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOEquipo.php";
require_once RUTA_MODELO . "/DAOReemplazo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/RegistradorErrores.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Registra reemplazos de componentes de equipos.
 */
class ControladorReemplazo
{
    private ConectorPDO $conectorPDO;
    private DAOEquipo $daoEquipo;
    private DAOReemplazo $dao;

    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];
        Sesion::verificarRolApi(["tecnico"]);

        if ($metodo === "POST") {
            Token::verificarCSRF();
        }

        try {
            if ($metodo !== "POST") {
                throw new Exception("Método no permitido.", 405);
            }

            $this->conectar();
            $resultado = $this->registrar();
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

        $this->daoEquipo = new DAOEquipo($conexion);
        $this->dao = new DAOReemplazo($conexion);
    }

    private function registrar(): array
    {
        $datosEnviados = json_decode(file_get_contents("php://input"), true);
        $datosEnviados = is_array($datosEnviados) ? $datosEnviados : [];
        $idEquipo = Validador::longitud(
            Validador::requerido($datosEnviados["idEquipo"] ?? "", "idEquipo"),
            1,
            10,
            "idEquipo"
        );

        if ($this->daoEquipo->obtener($idEquipo) === null) {
            throw new Exception("No existe un equipo con el identificador $idEquipo.", 404);
        }

        $idReemplazo = $this->dao->crear([
            "idEquipo" => $idEquipo,
            "componente" => Validador::longitud($datosEnviados["componente"] ?? "", 1, 100, "componente"),
            "descripcion" => Validador::longitud($datosEnviados["descripcion"] ?? "", 10, 2000, "descripción"),
            "cedulaTecnico" => $_SESSION["cedula"]
        ]);

        return [
            "datos" => $this->dao->obtener($idReemplazo),
            "mensaje" => "Reemplazo $idReemplazo registrado correctamente.",
            "codigo" => 201
        ];
    }
}

?>