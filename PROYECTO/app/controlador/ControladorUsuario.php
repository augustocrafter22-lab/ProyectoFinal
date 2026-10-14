<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOUsuario.php";
require_once RUTA_MODELO . "/ValidadorUsuario.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";

/**
 * Controlador unificado de USUARIO.
 *
 * Coordina las solicitudes HTTP, delegando las reglas de validación a
 * ValidadorUsuario, la persistencia a DAOUsuario y las respuestas a
 * RespuestaJson.
 */
class ControladorUsuario
{
    private DAOUsuario $dao;

    /**
     * Captura la solicitud y la deriva a la operación correspondiente.
     *
     * @return void
     */
    public function gestionar(): void
    {
        $metodo = $_SERVER["REQUEST_METHOD"];

        Sesion::verificarRolApi(["coordinador"]);

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
     * Establece la conexión y crea el DAO para la solicitud.
     *
     * @return void
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    private function conectar(): void
    {
        $conexion = Conexion::conectar();

        $this->dao = new DAOUsuario($conexion);
    }

    /**
     * Busca un usuario por cédula o devuelve el listado completo.
     *
     * @return array Resultado con datos, mensaje y código HTTP.
     */
    private function listar(): array
    {
        $cedula = ValidadorUsuario::texto($_GET["id"] ?? "");

        if ($cedula !== "") {
            $cedula = ValidadorUsuario::cedula($cedula);
            $usuario = $this->dao->obtener($cedula);

            if ($usuario === null) {
                throw new Exception("No existe un usuario con la cédula $cedula.", 404);
            }

            return ["datos" => $usuario, "mensaje" => "Usuario encontrado.", "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => "Listado de usuarios.", "codigo" => 200];
    }

    /**
     * Registra un usuario nuevo, siempre activo.
     *
     * @return array Resultado con datos, mensaje y código HTTP.
     */
    private function registrar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $datosUsuario = ValidadorUsuario::registro($datosEnviados);
        $cedula = $datosUsuario["cedula"];

        if ($this->dao->obtener($cedula) !== null) {
            throw new Exception("Ya existe un usuario con la cédula $cedula.", 409);
        }

        $this->dao->crear([
            "cedula" => $cedula,
            "nombre" => $datosUsuario["nombre"],
            "apellido" => $datosUsuario["apellido"],
            "claveHash" => password_hash($datosUsuario["clave"], PASSWORD_BCRYPT),
            "activo" => 1,
            "roles" => $datosUsuario["roles"]
        ]);

        return [
            "datos" => $this->dao->obtener($cedula),
            "mensaje" => "Usuario $cedula registrado correctamente.",
            "codigo" => 201
        ];
    }

    /**
     * Modifica un usuario existente, actualizando solo los campos recibidos.
     *
     * @return array Resultado con datos, mensaje y código HTTP.
     */
    private function actualizar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $cedula = ValidadorUsuario::cedula($_GET["id"] ?? ($datosEnviados["cedula"] ?? ""));

        if ($this->dao->obtener($cedula) === null) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 404);
        }

        $cambios = ValidadorUsuario::cambios($datosEnviados, $_SESSION["cedula"], $cedula);

        if (array_key_exists("clave", $cambios)) {
            $cambios["claveHash"] = password_hash($cambios["clave"], PASSWORD_BCRYPT);
            unset($cambios["clave"]);
        }

        $this->dao->actualizar($cedula, $cambios);

        $mensaje = "Usuario $cedula actualizado correctamente.";
        if (array_keys($cambios) === ["activo"]) {
            $mensaje = $cambios["activo"] === 1
                ? "Usuario $cedula activado correctamente."
                : "Usuario $cedula desactivado correctamente.";
        }

        return ["datos" => $this->dao->obtener($cedula), "mensaje" => $mensaje, "codigo" => 200];
    }

    /**
     * Elimina un usuario existente que no sea el usuario autenticado.
     *
     * @return array Resultado con datos, mensaje y código HTTP.
     */
    private function eliminar(): array
    {
        $datosEnviados = Solicitud::obtenerDatos();
        $cedula = ValidadorUsuario::texto($_GET["id"] ?? ($datosEnviados["cedula"] ?? ""));

        if ($cedula === "") {
            throw new Exception("Debe indicar la cédula del usuario.", 400);
        }

        $cedula = ValidadorUsuario::cedula($cedula);

        if ($this->dao->obtener($cedula) === null) {
            throw new Exception("No existe un usuario con la cédula $cedula.", 404);
        }

        if ($cedula === $_SESSION["cedula"]) {
            throw new Exception("No puede eliminar su propio usuario.", 409);
        }

        $this->dao->eliminar($cedula);

        return ["datos" => null, "mensaje" => "Usuario $cedula eliminado correctamente.", "codigo" => 200];
    }

}

?>
