<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOUsuario.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de USUARIO.
 *
 * Agrupa las funcionalidades que antes estaban repartidas en
 * procesarAltaUsuario, procesarEditarUsuario, procesarActivarUsuario y
 * procesarDesactivarUsuario en una sola clase.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado. Todo el endpoint es
 * exclusivo del coordinador, incluso para consultar:
 *
 *   GET    /public/api/usuarios.php               lista todos los usuarios
 *   GET    /public/api/usuarios.php?id=11111111   devuelve un usuario puntual
 *   POST   /public/api/usuarios.php               da de alta un usuario
 *   PUT    /public/api/usuarios.php?id=11111111   edita un usuario, activa o desactiva
 *   DELETE /public/api/usuarios.php?id=11111111   elimina un usuario
 *
 * Datos del usuario: cedula, nombre, apellido, activo (true o false) y roles
 * (arreglo con coordinador, tecnico y/o docente). En POST y PUT también se
 * recibe clave, que nunca se devuelve.
 *
 * En PUT solo se modifican los campos enviados, por eso para activar o
 * desactivar alcanza con enviar {"activo": true} o {"activo": false}.
 */
class ControladorUsuario
{
    private ConectorPDO $conectorPDO;
    private DAOUsuario $dao;

    /** @var array Roles permitidos para un usuario. */
    private array $rolesValidos = ["coordinador", "tecnico", "docente"];

    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        // La gestión de usuarios la hace solo el coordinador.
        Sesion::verificarRolApi(["coordinador"]);

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
            RegistradorErrores::registrar($e);

            // 23000 es el código SQLSTATE de una restricción de clave foránea:
            // el usuario tiene registros relacionados y la base no deja borrarlo.
            if ($metodo === "DELETE" && (string) $e->getCode() === "23000") {
                RespuestaJson::error("No se puede eliminar el usuario porque tiene registros relacionados.", 409);
            }

            RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
        } catch (Exception $e) {
            // Si el código de la excepción es un código HTTP válido se usa ese,
            // en cualquier otro caso se responde con un 500 (error del servidor).
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

        $this->dao = new DAOUsuario($conexion);
    }

    /**
     * Devuelve el listado de usuarios, o uno puntual si llega "id" por GET.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si la cédula es inválida o el usuario no existe.
     */
    private function listar(): array
    {
        $cedula = $this->texto($_GET["id"] ?? "");

        if ($cedula !== "") {
            $cedula = Validador::cedula($cedula);
            $usuario = $this->dao->obtener($cedula);

            if ($usuario === null) {
                throw new Exception("No existe un usuario con la cédula $cedula.", 404);
            }

            return ["datos" => $usuario, "mensaje" => "Usuario encontrado.", "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de usuarios.", "codigo" => 200];
    }

    /**
     * Da de alta un usuario nuevo, siempre queda activo.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos o el usuario ya existe.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $cedula = Validador::cedula($this->texto($datosEnviados["cedula"] ?? ""));
        $nombre = Validador::longitud($this->texto($datosEnviados["nombre"] ?? ""), 1, 12, "nombre");
        $apellido = Validador::longitud($this->texto($datosEnviados["apellido"] ?? ""), 1, 16, "apellido");
        $clave = Validador::requerido($this->texto($datosEnviados["clave"] ?? ""), "clave");
        $roles = $this->validarRoles($datosEnviados["roles"] ?? null);

        if ($this->dao->obtener($cedula) !== null) {
            throw new Exception("Ya existe un usuario con la cédula $cedula.", 409);
        }

        $this->dao->crear([
            "cedula" => $cedula,
            "nombre" => $nombre,
            "apellido" => $apellido,
            "claveHash" => password_hash($clave, PASSWORD_BCRYPT),
            "activo" => 1,
            "roles" => $roles
        ]);

        return [
            "datos" => $this->dao->obtener($cedula),
            "mensaje" => "Usuario $cedula registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Modifica un usuario existente. Solo se cambian los campos enviados:
     * nombre, apellido, clave, roles y activo.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son inválidos, el usuario no existe
     *                   o el coordinador intenta dejarse sin acceso.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $cedula = Validador::cedula($this->texto($_GET["id"] ?? ($datosEnviados["cedula"] ?? "")));

        if ($this->dao->obtener($cedula) === null) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 404);
        }

        $cambios = [];

        if (array_key_exists("nombre", $datosEnviados)) {
            $cambios["nombre"] = Validador::longitud($this->texto($datosEnviados["nombre"]), 1, 12, "nombre");
        }

        if (array_key_exists("apellido", $datosEnviados)) {
            $cambios["apellido"] = Validador::longitud($this->texto($datosEnviados["apellido"]), 1, 16, "apellido");
        }

        if (array_key_exists("clave", $datosEnviados)) {
            $clave = Validador::requerido($this->texto($datosEnviados["clave"]), "clave");
            $cambios["claveHash"] = password_hash($clave, PASSWORD_BCRYPT);
        }

        if (array_key_exists("roles", $datosEnviados)) {
            $cambios["roles"] = $this->validarRoles($datosEnviados["roles"]);
        }

        if (array_key_exists("activo", $datosEnviados)) {
            $cambios["activo"] = $this->validarActivo($datosEnviados["activo"]);
        }

        if (empty($cambios)) {
            throw new Exception("Debe enviar al menos un campo para modificar.", 400);
        }

        // Evita que el coordinador se bloquee a si mismo y el sistema se quede sin acceso.
        if ($cedula === $_SESSION["cedula"]) {
            if (isset($cambios["activo"]) && $cambios["activo"] === 0) {
                throw new Exception("No puede desactivar su propio usuario.", 409);
            }

            if (isset($cambios["roles"]) && !in_array("coordinador", $cambios["roles"], true)) {
                throw new Exception("No puede quitarse a si mismo el rol coordinador.", 409);
            }
        }

        $this->dao->actualizar($cedula, $cambios);

        // Si solo se envió "activo", el mensaje indica si se activó o desactivó.
        $mensaje = "Usuario $cedula actualizado correctamente.";

        if (array_keys($cambios) === ["activo"]) {
            $mensaje = $cambios["activo"] === 1
                ? "Usuario $cedula activado correctamente."
                : "Usuario $cedula desactivado correctamente.";
        }

        return ["datos" => $this->dao->obtener($cedula), "mensaje" => $mensaje, "codigo" => 200];
    }

    /**
     * Elimina un usuario existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica la cédula, el usuario no existe
     *                   o intenta eliminarse a si mismo.
     */
    private function eliminar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();

        $cedula = $this->texto($_GET["id"] ?? ($datosEnviados["cedula"] ?? ""));

        if ($cedula === "") {
            throw new Exception("Debe indicar la cédula del usuario.", 400);
        }

        $cedula = Validador::cedula($cedula);

        if ($this->dao->obtener($cedula) === null) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 404);
        }

        if ($cedula === $_SESSION["cedula"]) {
            throw new Exception("No puede eliminar su propio usuario.", 409);
        }

        $this->dao->eliminar($cedula);

        return ["datos" => null, "mensaje" => "Usuario $cedula eliminado correctamente.", "codigo" => 200];
    }

    /**
     * Valida que los roles sean un arreglo con al menos un rol permitido.
     *
     * @param mixed $roles Roles recibidos del cliente.
     * @return array Roles validados y sin repetir.
     * @throws Exception Si no es un arreglo, está vacío o trae un rol no permitido.
     */
    private function validarRoles($roles): array
    {
        if (!is_array($roles) || empty($roles)) {
            throw new Exception("Debe indicar al menos un rol.", 400);
        }

        $rolesValidados = [];

        foreach ($roles as $rol) {
            $rolesValidados[] = Validador::enLista($this->texto($rol), $this->rolesValidos, "roles");
        }

        return array_values(array_unique($rolesValidados));
    }

    /**
     * Valida el campo activo y lo convierte al valor que guarda la base.
     *
     * @param mixed $activo Valor recibido del cliente (true, false, 1 o 0).
     * @return int 1 si el usuario queda activo, 0 si queda inactivo.
     * @throws Exception Si el valor no es true, false, 1 o 0.
     */
    private function validarActivo($activo): int
    {
        if ($activo === true || $activo === 1 || $activo === "1") {
            return 1;
        }

        if ($activo === false || $activo === 0 || $activo === "0") {
            return 0;
        }

        throw new Exception("El campo activo debe ser true o false.", 400);
    }

    /**
     * Convierte un valor recibido a texto limpio. Si el cliente manda algo
     * que no es texto ni número (por ejemplo un arreglo) devuelve "" para
     * que la validación posterior lo rechace con un error 400.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @return string El valor como texto, con trim.
     */
    private function texto($valor): string
    {
        return is_scalar($valor) ? trim((string) $valor) : "";
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
