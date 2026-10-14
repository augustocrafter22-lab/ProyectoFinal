<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAODiagnostico.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de DIAGNOSTICO.
 *
 *   GET    /public/index.php?ruta=diagnosticos                lista todos los diagnósticos (admite ?idTicket= para filtrar)
 *   GET    /public/index.php?ruta=diagnosticos&id=5            devuelve un diagnóstico puntual
 *   POST   /public/index.php?ruta=diagnosticos                 registra un diagnóstico nuevo
 *   PUT    /public/index.php?ruta=diagnosticos&id=5             modifica un diagnóstico existente
 *   DELETE /public/index.php?ruta=diagnosticos&id=5             elimina un diagnóstico
 */
class ControladorDiagnostico
{
    private DAODiagnostico $dao;

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

        $this->dao = new DAODiagnostico($conexion);
    }

    /**
     * Devuelve el listado de diagnósticos, o uno puntual si llega "id" por GET.
     * Admite filtrar el listado por "idTicket".
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el diagnóstico solicitado no existe.
     */
    private function listar(): array
    {
        $id = trim($_GET["id"] ?? "");

        if ($id !== "") {
            $idDiagnostico = (int) Validador::numerico($id, "id");
            $diagnostico = $this->dao->obtener($idDiagnostico);

            if ($diagnostico === null) {
                throw new Exception("No existe un diagnóstico con el identificador $idDiagnostico.", 404);
            }

            return ["datos" => $diagnostico, "mensaje" => "Diagnóstico encontrado.", "codigo" => 200];
        }

        $idTicket = trim($_GET["idTicket"] ?? "");

        return [
            "datos" => $this->dao->listar($idTicket !== "" ? $idTicket : null),
            "mensaje" => "Listado de diagnósticos.",
            "codigo" => 200
        ];
    }

    /**
     * Registra un diagnóstico nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el ticket no existe.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos(Solicitud::obtenerDatos());

        $idDiagnostico = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idDiagnostico),
            "mensaje" => "Diagnóstico $idDiagnostico registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza el texto de un diagnóstico existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el diagnóstico no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $idDiagnostico = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idDiagnostico"] ?? ""), "idDiagnostico");

        if ($this->dao->obtener($idDiagnostico) === null) {
            throw new Exception("No existe un diagnóstico con el identificador $idDiagnostico.", 404);
        }

        $diagnostico = Validador::longitud($datosEnviados["diagnostico"] ?? "", 10, 2000, "diagnóstico");

        $this->dao->actualizar($idDiagnostico, ["diagnostico" => $diagnostico]);

        return [
            "datos" => $this->dao->obtener($idDiagnostico),
            "mensaje" => "Diagnóstico $idDiagnostico actualizado correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina un diagnóstico existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o el diagnóstico no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $idDiagnostico = (int) Validador::numerico($_GET["id"] ?? ($datosEnviados["idDiagnostico"] ?? ""), "idDiagnostico");

        if ($this->dao->obtener($idDiagnostico) === null) {
            throw new Exception("No existe un diagnóstico con el identificador $idDiagnostico.", 404);
        }

        $this->dao->eliminar($idDiagnostico);

        return ["datos" => null, "mensaje" => "Diagnóstico $idDiagnostico eliminado correctamente.", "codigo" => 200];
    }

    /**
     * Valida los datos de alta de un diagnóstico.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el ticket no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $idTicket = Validador::requerido($datosEnviados["idTicket"] ?? "", "idTicket");
        $cedulaTecnico = Validador::cedula($datosEnviados["cedulaTecnico"] ?? "");
        $diagnostico = Validador::longitud($datosEnviados["diagnostico"] ?? "", 10, 2000, "diagnóstico");

        if (!$this->dao->existeTicket($idTicket)) {
            throw new Exception("No existe el ticket $idTicket.", 400);
        }

        return [
            "idTicket" => $idTicket,
            "cedulaTecnico" => $cedulaTecnico,
            "diagnostico" => $diagnostico
        ];
    }

}

?>
