<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAODiagnostico.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

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

        // El diagnóstico queda a nombre del técnico que lo registra, por eso solo un técnico puede darlo de alta.
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
                throw new Exception(Traductor::t("diagnosticos.noExiste", ["id" => $idDiagnostico]), 404);
            }

            return ["datos" => $diagnostico, "mensaje" => Traductor::t("diagnosticos.encontrado"), "codigo" => 200];
        }

        $idTicket = trim($_GET["idTicket"] ?? "");

        return [
            "datos" => $this->dao->listar($idTicket !== "" ? $idTicket : null),
            "mensaje" => Traductor::t("diagnosticos.listado"),
            "codigo" => 200
        ];
    }

    /**
     * Registra un diagnóstico nuevo a nombre del técnico que inició sesión.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el ticket no existe.
     */
    private function registrar(): array
    {
        $datos = $this->validarDatos(Solicitud::obtenerDatos());
        // La cédula sale de la sesión y no del cliente, así nadie registra a nombre de otro técnico.
        $datos["cedulaTecnico"] = Sesion::cedula();

        $idDiagnostico = $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idDiagnostico),
            "mensaje" => Traductor::t("diagnosticos.registrado", ["id" => $idDiagnostico]),
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
            throw new Exception(Traductor::t("diagnosticos.noExiste", ["id" => $idDiagnostico]), 404);
        }

        $diagnostico = Validador::longitud($datosEnviados["diagnostico"] ?? "", 10, 2000, "diagnostico");

        $this->dao->actualizar($idDiagnostico, ["diagnostico" => $diagnostico]);

        return [
            "datos" => $this->dao->obtener($idDiagnostico),
            "mensaje" => Traductor::t("diagnosticos.actualizado", ["id" => $idDiagnostico]),
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
            throw new Exception(Traductor::t("diagnosticos.noExiste", ["id" => $idDiagnostico]), 404);
        }

        $this->dao->eliminar($idDiagnostico);

        return ["datos" => null, "mensaje" => Traductor::t("diagnosticos.eliminado", ["id" => $idDiagnostico]), "codigo" => 200];
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
        $diagnostico = Validador::longitud($datosEnviados["diagnostico"] ?? "", 10, 2000, "diagnostico");

        if (!$this->dao->existeTicket($idTicket)) {
            throw new Exception(Traductor::t("tickets.noExiste", ["id" => $idTicket]), 400);
        }

        return [
            "idTicket" => $idTicket,
            "diagnostico" => $diagnostico
        ];
    }

}

?>
