<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOEquipo.php";
require_once RUTA_VISTA . "/RespuestaJson.php";

/**
 * Controlador unificado de EQUIPO.
 *
 * Agrupa las funcionalidades que antes estaban repartidas en
 * cargarEquipos y procesarEquipo (alta, modificación y baja) en una
 * sola clase.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado:
 *
 *   GET    /public/api/equipos.php            lista todos los equipos
 *   GET    /public/api/equipos.php?id=PC-01   devuelve un equipo puntual
 *   POST   /public/api/equipos.php            registra un equipo nuevo
 *   PUT    /public/api/equipos.php?id=PC-01   modifica un equipo existente
 *   DELETE /public/api/equipos.php?id=PC-01   elimina un equipo
 */
class ControladorEquipo
{
    private ConectorPDO $conectorPDO;
    private DAOEquipo $dao;

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
            RespuestaJson::error($e->getMessage(), $codigo);
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

        $this->dao = new DAOEquipo($conexion);
    }

    /**
     * Devuelve el listado de equipos, o uno especifico si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si el equipo solicitado no existe.
     */
    private function listar(): array
    {
        $idEquipo = trim($_GET["id"] ?? "");

        if ($idEquipo !== "") {
            $equipo = $this->dao->obtener($idEquipo);

            if ($equipo === null) {
                throw new Exception("No existe un equipo con el identificador $idEquipo.", 404);
            }

            return ["datos" => $equipo, "mensaje" => "Equipo encontrado.", "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de equipos.", "codigo" => 200];
    }

    /**
     * Registra un equipo nuevo con los datos enviados por el cliente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el equipo ya existe.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idEquipo = trim($datosEnviados["idEquipo"] ?? "");

        if ($idEquipo === "") {
            throw new Exception("El campo idEquipo es obligatorio.", 400);
        }

        if ($this->dao->obtener($idEquipo) !== null) {
            throw new Exception("Ya existe un equipo con el identificador $idEquipo.", 409);
        }

        $datos = $this->validarDatos($datosEnviados);
        $datos["idEquipo"] = $idEquipo;

        $this->dao->crear($datos);

        return [
            "datos" => $this->dao->obtener($idEquipo),
            "mensaje" => "Equipo $idEquipo registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Actualiza los datos de un equipo existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el equipo no existe.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idEquipo = trim($_GET["id"] ?? ($datosEnviados["idEquipo"] ?? ""));

        if ($idEquipo === "") {
            throw new Exception("Debe indicar el identificador del equipo.", 400);
        }

        if ($this->dao->obtener($idEquipo) === null) {
            throw new Exception("No existe un equipo con el identificador $idEquipo.", 404);
        }

        $datos = $this->validarDatos($datosEnviados);

        $this->dao->actualizar($idEquipo, $datos);

        return [
            "datos" => $this->dao->obtener($idEquipo),
            "mensaje" => "Equipo $idEquipo actualizado correctamente.",
            "codigo" => 200
        ];
    }

    /**
     * Elimina un equipo existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica el id o el equipo no existe.
     */
    private function eliminar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $idEquipo = trim($_GET["id"] ?? ($datosEnviados["idEquipo"] ?? ""));

        if ($idEquipo === "") {
            throw new Exception("Debe indicar el identificador del equipo.", 400);
        }

        if ($this->dao->obtener($idEquipo) === null) {
            throw new Exception("No existe un equipo con el identificador $idEquipo.", 404);
        }

        $this->dao->eliminar($idEquipo);

        return ["datos" => null, "mensaje" => "Equipo $idEquipo eliminado correctamente.", "codigo" => 200];
    }

    /**
     * Valida los datos de alta y la modificación de un equipo.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return array Arreglo con los campos ya validados.
     * @throws Exception Si falta un campo obligatorio o el laboratorio no existe.
     */
    private function validarDatos(array $datosEnviados): array
    {
        $campos = ["idLaboratorio", "marca", "estado", "disponibilidad"];
        $datos = [];

        foreach ($campos as $campo) {
            $valor = trim($datosEnviados[$campo] ?? "");

            if ($valor === "") {
                throw new Exception("El campo $campo es obligatorio.", 400);
            }

            $datos[$campo] = $valor;
        }

        if (!$this->dao->existeLaboratorio($datos["idLaboratorio"])) {
            throw new Exception("No existe el laboratorio " . $datos["idLaboratorio"] . ".", 400);
        }

        $datos["informacion"] = trim($datosEnviados["informacion"] ?? "");

        return $datos;
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