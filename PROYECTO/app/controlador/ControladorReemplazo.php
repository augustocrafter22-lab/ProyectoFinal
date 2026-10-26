<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOEquipo.php";
require_once RUTA_MODELO . "/DAOReemplazo.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Registra reemplazos de componentes de equipos.
 */
class ControladorReemplazo
{
    private DAOEquipo $daoEquipo;
    private DAOReemplazo $dao;

    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];
        Sesion::verificarRolApi(["tecnico"]);


        if ($metodo !== "POST") {
            throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }

        $this->conectar();
        $resultado = $this->registrar();
        Conexion::desconectar();

        RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);
    }

    private function conectar(): void
    {
        $conexion = Conexion::conectar();

        $this->daoEquipo = new DAOEquipo($conexion);
        $this->dao = new DAOReemplazo($conexion);
    }

    private function registrar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $idEquipo = Validador::longitud(
            Validador::requerido($datosEnviados["idEquipo"] ?? "", "idEquipo"),
            1,
            10,
            "idEquipo"
        );

        if ($this->daoEquipo->obtener($idEquipo) === null) {
            throw new Exception(Traductor::t("equipos.noExiste", ["id" => $idEquipo]), 404);
        }

        $idReemplazo = $this->dao->crear([
            "idEquipo" => $idEquipo,
            "componente" => Validador::longitud($datosEnviados["componente"] ?? "", 1, 100, "componente"),
            "descripcion" => Validador::longitud($datosEnviados["descripcion"] ?? "", 10, 2000, "descripcion"),
            "cedulaTecnico" => Sesion::cedula()
        ]);

        return [
            "datos" => $this->dao->obtener($idReemplazo),
            "mensaje" => Traductor::t("reemplazos.registrado", ["id" => $idReemplazo]),
            "codigo" => 201
        ];
    }
}

?>