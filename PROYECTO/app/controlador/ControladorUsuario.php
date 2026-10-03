<?php
 
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/DAOUsuario.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_MODELO . "/RegistradorErrores.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_MODELO . "/Sesion.php";
require_once RUTA_MODELO . "/Token.php";
 
/**
 * Controlador unificado de USUARIO.
 *
 * Agrupa las funcionalidades que antes estaban repartidas en
 * procesarAltaUsuario, procesarEditarUsuario, procesarActivarUsuario y
 * procesarDesactivarUsuario en una sola clase.
 *
 * Captura la solicitud del cliente mediante el método gestionar(), que
 * decide qué hacer según el método HTTP utilizado. Solo el coordinador
 * puede usar este endpoint, en todos los métodos:
 *
 *   GET    /public/api/usuarios.php               lista todos los usuarios
 *   GET    /public/api/usuarios.php?id=11111111   devuelve un usuario puntual
 *   POST   /public/api/usuarios.php               da de alta un usuario
 *   PUT    /public/api/usuarios.php?id=11111111   edita un usuario (parcial)
 *   DELETE /public/api/usuarios.php?id=11111111   elimina un usuario
 *
 * Contrato JSON del usuario: cedula, nombre, apellido, activo (bool) y
 * roles (arreglo con coordinador, tecnico y/o docente). En POST y PUT
 * también se acepta contrasenia, que nunca se devuelve.
 *
 * Activar y desactivar se hacen con PUT enviando {"activo": true} o
 * {"activo": false}.
 */
class ControladorUsuario
{
    private ConectorPDO $conectorPDO;
    private DAOUsuario $dao;
 
    /**
     * Captura la solicitud del cliente y la deriva al método correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];
 
        // La gestión de usuarios es exclusiva del coordinador, incluso para consultar.
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
            // Los errores de la base de datos traen un código SQLSTATE (por ejemplo "23000"),
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
        } catch (Throwable $e) {
            // Errores de tipo (TypeError, etc.): se registran y no se muestran al cliente.
            error_log("Error: " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine());
            RespuestaJson::error("Ocurrió un error, intente nuevamente.", 500);
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
     * Devuelve el listado de usuarios, o uno específico si llega "id" por GET.
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
     * Da de alta un usuario nuevo (siempre queda activo).
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos o el usuario ya existe.
     */
    private function registrar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();
 
        $cedula = Validador::cedula($this->texto($datosEnviados["cedula"] ?? ""));
        $nombre = Validador::longitud($this->texto($datosEnviados["nombre"] ?? ""), 1, 12, "nombre");
        $apellido = Validador::longitud($this->texto($datosEnviados["apellido"] ?? ""), 1, 16, "apellido");
        $clave = Validador::requerido($this->texto($datosEnviados["contrasenia"] ?? ""), "contrasenia");
        $roles = $this->validarRoles($datosEnviados["roles"] ?? null);
 
        if ($this->dao->obtener($cedula) !== null) {
            throw new Exception("Ya existe un usuario con la cédula $cedula.", 409);
        }
 
        $this->dao->crear([
            "cedula" => $cedula,
            "nombre" => $nombre,
            "apellido" => $apellido,
            "claveHash" => password_hash($clave, PASSWORD_BCRYPT),
            "roles" => $roles
        ]);
 
        return [
            "datos" => $this->dao->obtener($cedula),
            "mensaje" => "Usuario $cedula creado correctamente.",
            "codigo" => 201
        ];
    }
 
    /**
     * Edita un usuario existente. Solo se modifican los campos enviados:
     * nombre, apellido, contrasenia, roles y activo.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si los datos son incorrectos, el usuario no existe
     *                   o la operación dejaría al coordinador sin acceso.
     */
    private function actualizar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();
        $cedula = $this->obtenerCedulaIndicada($datosEnviados);
 
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
        if (array_key_exists("contrasenia", $datosEnviados)) {
            $clave = Validador::requerido($this->texto($datosEnviados["contrasenia"]), "contrasenia");
            $cambios["claveHash"] = password_hash($clave, PASSWORD_BCRYPT);
        }
        if (array_key_exists("roles", $datosEnviados)) {
            $cambios["roles"] = $this->validarRoles($datosEnviados["roles"]);
        }
        if (array_key_exists("activo", $datosEnviados)) {
            $cambios["activo"] = $this->validarActivo($datosEnviados["activo"]);
        }
 
        if (empty($cambios)) {
            throw new Exception("Debe enviar al menos un campo para modificar (nombre, apellido, contrasenia, roles o activo).", 400);
        }
 
        // Evita que el coordinador se bloquee a sí mismo.
        if ($cedula === $_SESSION["cedula"]) {
            if (isset($cambios["activo"]) && $cambios["activo"] === false) {
                throw new Exception("No puede desactivar su propio usuario.", 409);
            }
            if (isset($cambios["roles"]) && !in_array("coordinador", $cambios["roles"], true)) {
                throw new Exception("No puede quitarse a sí mismo el rol de coordinador.", 409);
            }
        }
 
        try {
            $this->dao->actualizar($cedula, $cambios);
        } catch (PDOException $e) {
            // SQLSTATE 23000: quitar un rol que otras tablas todavía referencian
            // (por ejemplo, un técnico con diagnósticos registrados).
            if ($e->getCode() === "23000") {
                throw new Exception("No se puede quitar un rol que el usuario ya utilizó en el sistema.", 409);
            }
            throw $e;
        }
 
        $soloActivacion = array_keys($cambios) === ["activo"];
 
        if ($soloActivacion) {
            $mensaje = $cambios["activo"] ? "Usuario $cedula activado correctamente." : "Usuario $cedula desactivado correctamente.";
        } else {
            $mensaje = "Usuario $cedula actualizado correctamente.";
        }
 
        return ["datos" => $this->dao->obtener($cedula), "mensaje" => $mensaje, "codigo" => 200];
    }
 
    /**
     * Elimina un usuario existente.
     *
     * @return array Arreglo con las claves datos, mensaje y codigo.
     * @throws Exception Si no se indica la cédula, el usuario no existe,
     *                   es el propio coordinador o tiene registros asociados.
     */
    private function eliminar(): array
    {
        $datosEnviados = $this->obtenerDatosEnviados();
        $cedula = $this->obtenerCedulaIndicada($datosEnviados);
 
        if ($this->dao->obtener($cedula) === null) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 404);
        }
 
        if ($cedula === $_SESSION["cedula"]) {
            throw new Exception("No puede eliminar su propio usuario.", 409);
        }
 
        try {
            $this->dao->eliminar($cedula);
        } catch (PDOException $e) {
            // SQLSTATE 23000: el usuario tiene diagnósticos, reparaciones, soluciones,
            // solicitudes o préstamos que lo referencian.
            if ($e->getCode() === "23000") {
                throw new Exception("No se puede eliminar un usuario con registros asociados. Desactívelo en su lugar.", 409);
            }
            throw $e;
        }
 
        return ["datos" => null, "mensaje" => "Usuario $cedula eliminado correctamente.", "codigo" => 200];
    }
 
    /**
     * Obtiene y valida la cédula indicada por "id" en la URL, o "cedula" en el cuerpo.
     *
     * @param array $datosEnviados Datos recibidos del cliente.
     * @return string Cédula válida.
     * @throws Exception Si falta o tiene un formato incorrecto.
     */
    private function obtenerCedulaIndicada(array $datosEnviados): string
    {
        $cedula = $this->texto($_GET["id"] ?? ($datosEnviados["cedula"] ?? ""));
 
        if ($cedula === "") {
            throw new Exception("Debe indicar la cédula del usuario.", 400);
        }
 
        return Validador::cedula($cedula);
    }
 
    /**
     * Valida el arreglo de roles: no vacío, sin repetidos y solo roles conocidos.
     *
     * @param mixed $roles Valor recibido del cliente.
     * @return array Roles validados.
     * @throws Exception Si no es un arreglo, está vacío o trae un rol inválido.
     */
    private function validarRoles($roles): array
    {
        if (!is_array($roles) || empty($roles)) {
            throw new Exception("Debe indicar al menos un rol.", 400);
        }
 
        $roles = array_values(array_unique($roles));
 
        foreach ($roles as $rol) {
            if (!is_string($rol) || !in_array($rol, DAOUsuario::rolesPermitidos(), true)) {
                throw new Exception("El campo roles tiene un valor no permitido. Use: " . implode(", ", DAOUsuario::rolesPermitidos()) . ".", 400);
            }
        }
 
        return $roles;
    }
 
    /**
     * Convierte el valor de "activo" a booleano (acepta true/false, 1/0 y "true"/"false").
     *
     * @param mixed $valor Valor recibido del cliente.
     * @return bool El valor booleano.
     * @throws Exception Si no se puede interpretar como booleano.
     */
    private function validarActivo($valor): bool
    {
        $booleano = filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
 
        if ($booleano === null) {
            throw new Exception("El campo activo debe ser true o false.", 400);
        }
 
        return $booleano;
    }
 
    /**
     * Convierte un valor recibido a texto sin romper si llega un arreglo u objeto,
     * para que Validador responda con un 400 en lugar de un error de tipo.
     *
     * @param mixed $valor Valor recibido del cliente.
     * @return string El texto con trim, o "" si no es un valor escalar.
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
 