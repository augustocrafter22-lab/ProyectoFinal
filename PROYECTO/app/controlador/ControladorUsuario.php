<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_MODELO . "/DAOUsuario.php";
require_once RUTA_MODELO . "/ValidadorUsuario.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

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
                throw new Exception(Traductor::t("usuarios.noExiste", ["cedula" => $cedula]), 404);
            }

            return ["datos" => $usuario, "mensaje" => Traductor::t("usuarios.encontrado"), "codigo" => 200];
        }

        return ["datos" => $this->dao->listar(), "mensaje" => Traductor::t("usuarios.listado"), "codigo" => 200];
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
            throw new Exception(Traductor::t("usuarios.yaExiste", ["cedula" => $cedula]), 409);
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
            "mensaje" => Traductor::t("usuarios.registrado", ["cedula" => $cedula]),
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
            throw new Exception(Traductor::t("usuarios.noExiste", ["cedula" => $cedula]), 404);
        }

        $cambios = ValidadorUsuario::cambios($datosEnviados, Sesion::cedula(), $cedula);

        if (array_key_exists("clave", $cambios)) {
            $cambios["claveHash"] = password_hash($cambios["clave"], PASSWORD_BCRYPT);
            unset($cambios["clave"]);
        }

        $this->dao->actualizar($cedula, $cambios);

        $mensaje = Traductor::t("usuarios.actualizado", ["cedula" => $cedula]);
        if (array_keys($cambios) === ["activo"]) {
            $mensaje = $cambios["activo"] === 1
                ? Traductor::t("usuarios.activado", ["cedula" => $cedula])
                : Traductor::t("usuarios.desactivado", ["cedula" => $cedula]);
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
            throw new Exception(Traductor::t("usuarios.faltaCedula"), 400);
        }

        $cedula = ValidadorUsuario::cedula($cedula);

        if ($this->dao->obtener($cedula) === null) {
            throw new Exception(Traductor::t("usuarios.noExiste", ["cedula" => $cedula]), 404);
        }

        if ($cedula === Sesion::cedula()) {
            throw new Exception(Traductor::t("usuarios.noEliminarse"), 409);
        }

        $this->dao->eliminar($cedula);

        return ["datos" => null, "mensaje" => Traductor::t("usuarios.eliminado", ["cedula" => $cedula]), "codigo" => 200];
    }

}

?>
