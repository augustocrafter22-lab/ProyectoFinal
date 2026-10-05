<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOSolucion.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/RegistradorErrores.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de SOLUCION.
 *
 *   GET    /public/api/soluciones.php                lista todas las soluciones
 *   GET    /public/api/soluciones.php?id=5            devuelve una solución puntual
 *   POST   /public/api/soluciones.php                 registra una solución nueva
 *   PUT    /public/api/soluciones.php?id=5             modifica una solución existente
 *   DELETE /public/api/soluciones.php?id=5             elimina una solución
 */
class ControladorSolucion
{
    private ConectorPDO $conectorPDO;
    private DAOSolucion $dao;

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        Sesion::verificarRolApi(["coordinador", "tecnico"]);

        // GET no modifica datos, por eso solo se pide el token en POST, PUT y DELETE.
        if (in_array($metodo, ["POST", "PUT", "DELETE"], true)) {
            Token::verificarCSRF();
        }

        try {
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
                    throw new Exception("Método no permitido.", 405);
            }

            $this->conectorPDO->desconectar();

            RespuestaJson::exito($resultado["datos"], $resultado["mensaje"], $resultado["codigo"]);

        } catch (PDOException $e) {
            // Los errores de la base de datos traen un código SQLSTATE (por ejemplo "42S02"),
            // que no es un código HTTP, por eso siempre se responde con un 500.
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
                throw new Exception("No existe una solución con el identificador $idSolucion.", 404);
            }

            return ["datos" => $solucion, "mensaje" => "Solución encontrada.", "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de soluciones.", "codigo" => 200];
    }

    /**
     * Registra una solución nueva con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el diagnóstico no existe.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos($this->obtenerDatosEnviados());

        $idSolucion = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idSolucion),
            "mensaje" => "Solución $idSolucion registrada correctamente.",
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
        $datosEnviados = $this->obtenerDatosEnviados();
        $idSolucion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idSolucion"] ?? ""), "idSolucion");

        if ($this->dao->obtener($idSolucion) === null) {
            throw new Exception("No existe una solución con el identificador $idSolucion.", 404);
        }

        $solucion = Validador::longitud($datosEnviados["solucion"] ?? "", 10, 2000, "solución");

        $this->dao->actualizar($idSolucion, ["solucion" => $solucion]);

        return [
            "datos" => $this->dao->obtener($idSolucion),
            "mensaje" => "Solución $idSolucion actualizada correctamente.",
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
        $datosEnviados = $this->obtenerDatosEnviados();
        $idSolucion = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idSolucion"] ?? ""), "idSolucion");

        if ($this->dao->obtener($idSolucion) === null) {
            throw new Exception("No existe una solución con el identificador $idSolucion.", 404);
        }

        $this->dao->eliminar($idSolucion);

        return ["datos" => null, "mensaje" => "Solución $idSolucion eliminada correctamente.", "codigo" => 200];
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
        $cedulaTecnico = Validador::cedula($datosEnviados["cedulaTecnico"] ?? "");
        $solucion = Validador::longitud($datosEnviados["solucion"] ?? "", 10, 2000, "solución");

        if (!$this->dao->existeDiagnostico($idDiagnostico)) {
            throw new Exception("No existe el diagnóstico $idDiagnostico.", 400);
        }

        return [
            "idDiagnostico" => $idDiagnostico,
            "cedulaTecnico" => $cedulaTecnico,
            "solucion" => $solucion
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
