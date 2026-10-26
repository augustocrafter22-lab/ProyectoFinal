<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOSolucion.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador unificado de SOLUCION.
 *
 *   GET    /public/index.php?ruta=soluciones                lista todas las soluciones
 *   GET    /public/index.php?ruta=soluciones&id=5            devuelve una solución puntual
 *   POST   /public/index.php?ruta=soluciones                 registra una solución nueva
 *   PUT    /public/index.php?ruta=soluciones&id=5             modifica una solución existente
 *   DELETE /public/index.php?ruta=soluciones&id=5             elimina una solución
 */
class ControladorSolucion
{
    private DAOSolucion $dao;

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // La solución queda a nombre del técnico que la registra, por eso solo un técnico puede darla de alta.
        if ($metodo === "POST") {
            Sesion::verificarRolApi(["tecnico"]);
        } else {
            Sesion::verificarRolApi(["coordinador", "tecnico"]);
        }

        $this->conectar();

        switch ($metodo) {
            case "GET":
                $resultado = $this->listar();
                break;
            case "POST":
                $resultado = $this->registrar();
                break;
            case "PUT":
                $resultado = $this->actualizar();
                break;
            case "DELETE":
                $resultado = $this->eliminar();
                break;
            default:
                throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }

        Conexion::desconectar();

        RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);
    }

    /**
     * Establece la conexión con la base de datos y crea el DAO.
     *
     * @return void
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    private function conectar(): void
    {
        $conexion = Conexion::conectar();

        $this->dao = new DAOSolucion($conexion);
    }

    /**
     * Devuelve el listado de soluciones, o una puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si la solución solicitada no existe.
     */
    private function listar(): array
    {
        $id = trim($_GET["id"] ?? "");

        if ($id !== "") {
            $idSolucion = (int) Validador::numerico($id, "id");
            $solucion = $this->dao->obtener($idSolucion);

            if ($solucion === null) {
                throw new Exception(Traductor::t("soluciones.noExiste", ["id" => $idSolucion]), 404);
            }

            return ["datos" => $solucion, "mensaje" => Traductor::t("soluciones.encontrado"), "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => Traductor::t("soluciones.listado"), "codigo" => 200];
    }

    /**
     * Registra una solución nueva a nombre del técnico que inició sesión.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el diagnóstico no existe.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos(Solicitud::obtenerDatos());
        // La cédula sale de la sesión y no del cliente, así nadie registra a nombre de otro técnico.
        $datos["cedulaTecnico"] = Sesion::cedula();

        $idSolucion = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idSolucion),
            "mensaje" => Traductor::t("soluciones.registrado", ["id" => $idSolucion]),
            "codigo" => 201
        ];
    }

    /**
     * Actualiza el texto de una solución existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o la solución no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $idSolucion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idSolucion"] ?? ""), "idSolucion");

        if ($this->dao->obtener($idSolucion) === null) {
            throw new Exception(Traductor::t("soluciones.noExiste", ["id" => $idSolucion]), 404);
        }

        $solucion = Validador::longitud($datosEnviados["solucion"] ?? "", 10, 2000, "solucion");

        $this->dao->actualizar($idSolucion, ["solucion" => $solucion]);

        return [
            "datos" => $this->dao->obtener($idSolucion),
            "mensaje" => Traductor::t("soluciones.actualizado", ["id" => $idSolucion]),
            "codigo" => 200
        ];
    }

    /**
     * Elimina una solución existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o la solución no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $idSolucion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idSolucion"] ?? ""), "idSolucion");

        if ($this->dao->obtener($idSolucion) === null) {
            throw new Exception(Traductor::t("soluciones.noExiste", ["id" => $idSolucion]), 404);
        }

        $this->dao->eliminar($idSolucion);

        return ["datos" => null, "mensaje" => Traductor::t("soluciones.eliminado", ["id" => $idSolucion]), "codigo" => 200];
    }

    /**
     * Valida los datos de alta de una solución.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el diagnóstico no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $idDiagnostico = (int) Validador::numerico($datosEnviados["idDiagnostico"] ?? "", "idDiagnostico");
        $solucion = Validador::longitud($datosEnviados["solucion"] ?? "", 10, 2000, "solucion");

        if (!$this->dao->existeDiagnostico($idDiagnostico)) {
            throw new Exception(Traductor::t("diagnosticos.noExiste", ["id" => $idDiagnostico]), 400);
        }

        return [
            "idDiagnostico" => $idDiagnostico,
            "solucion" => $solucion
        ];
    }

}

?>
