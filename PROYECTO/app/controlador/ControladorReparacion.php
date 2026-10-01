<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOReparacion.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/RegistradorErrores.php";
require_once RUTA_VISTA . "/RespuestaJson.php";

/**
 * Controlador unificado de REPARACION.
 *
 *   GET    /public/api/reparaciones.php                   lista todas las reparaciones
 *   GET    /public/api/reparaciones.php?idEquipo=PC-01     historial técnico de un equipo puntual
 *   GET    /public/api/reparaciones.php?id=5                devuelve una reparación puntual
 *   POST   /public/api/reparaciones.php                    registra una reparación nueva
 *   PUT    /public/api/reparaciones.php?id=5                modifica una reparación existente
 *   DELETE /public/api/reparaciones.php?id=5                elimina una reparación
 */
class ControladorReparacion
{
    private ConectorPDO $conectorPDO;
    private DAOReparacion $dao;

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        try {
            $this->conectar();

            switch ($_SERVER["REQUEST_METHOD"]) {
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
                    throw new Exception("Método no permitido.", 405);
            }

            $this->conectorPDO->desconectar();

            RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);

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

    /**
     * Establece la conexión con la base de datos y crea el DAO.
     *
     * @return void
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    private function conectar(): void
    {
        $this->conectorPDO = new ConectorPDO($_ENV["BD_HOST"], $_ENV["BD_USER"], $_ENV["BD_PASS"], $_ENV["BD_NAME"]);
        $conexion = $this->conectorPDO->establecerConexion();

        if ($conexion === null) {
            throw new Exception("No se pudo conectar a la base de datos.", 500);
        }

        $this->dao = new DAOReparacion($conexion);
    }

    /**
     * Devuelve el listado de reparaciones, una puntual si llega "id" por GET,
     * o el historial técnico de un equipo si llega "idEquipo" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si la reparación solicitada no existe.
     */
    private function listar(): array
    {
        $id = trim($_GET["id"] ?? "");

        if ($id !== "") {
            $idReparacion = (int) Validador::numerico($id, "id");
            $reparacion = $this->dao->obtener($idReparacion);

            if ($reparacion === null) {
                throw new Exception("No existe una reparación con el identificador $idReparacion.", 404);
            }

            return ["datos" => $reparacion, "mensaje" => "Reparación encontrada.", "codigo" => 200];
        }

        $idEquipo = trim($_GET["idEquipo"] ?? "");

        if ($idEquipo !== "") {
            return [
                "datos" => $this->dao->listar($idEquipo),
                "mensaje" => "Historial técnico del equipo $idEquipo.",
                "codigo" => 200
            ];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de reparaciones.", "codigo" => 200];
    }

    /**
     * Registra una reparación nueva con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el diagnóstico no existe.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos($this->obtenerDatosEnviados());

        $idReparacion = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idReparacion),
            "mensaje" => "Reparación $idReparacion registrada correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza el texto de una reparación existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o la reparación no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();
        $idReparacion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idReparacion"] ?? ""), "idReparacion");

        if ($this->dao->obtener($idReparacion) === null) {
            throw new Exception("No existe una reparación con el identificador $idReparacion.", 404);
        }

        $reparacion = Validador::longitud($datosEnviados["reparacion"] ?? "", 10, 2000, "reparación");

        $this->dao->actualizar($idReparacion, ["reparacion" => $reparacion]);

        return [
            "datos" => $this->dao->obtener($idReparacion),
            "mensaje" => "Reparación $idReparacion actualizada correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina una reparación existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o la reparación no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();
        $idReparacion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idReparacion"] ?? ""), "idReparacion");

        if ($this->dao->obtener($idReparacion) === null) {
            throw new Exception("No existe una reparación con el identificador $idReparacion.", 404);
        }

        $this->dao->eliminar($idReparacion);

        return ["datos" => null, "mensaje" => "Reparación $idReparacion eliminada correctamente.", "codigo" => 200];
    }

    /**
     * Valida los datos de alta de una reparación.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el diagnóstico no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $idDiagnostico = (int) Validador::numerico($datosEnviados["idDiagnostico"] ?? "", "idDiagnostico");
        $cedulaTecnico = Validador::cedula($datosEnviados["cedulaTecnico"] ?? "");
        $reparacion = Validador::longitud($datosEnviados["reparacion"] ?? "", 10, 2000, "reparación");

        if (!$this->dao->existeDiagnostico($idDiagnostico)) {
            throw new Exception("No existe el diagnóstico $idDiagnostico.", 400);
        }

        return [
            "idDiagnostico" => $idDiagnostico,
            "cedulaTecnico" => $cedulaTecnico,
            "reparacion" => $reparacion
        ];
    }

    /**
     * Obtiene los datos enviados por el cliente.
     *
     * Acepta tanto formato JSON como los datos de un formulario $_POST.
     *
     * @return array Arreglo asociativo con los datos recibidos.
     */
    private function obtenerDatosEnviados(): array
    {
        $cuerpo = file_get_contents("php://input");
        $datos = json_decode($cuerpo, true);

        if (is_array($datos)) {
            return $datos;
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        parse_str($cuerpo, $datosFormulario);

        return is_array($datosFormulario) ? $datosFormulario : [];
    }
}

?>
