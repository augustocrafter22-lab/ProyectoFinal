<?php

require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Login.php";
require_once RUTA_MODELO . "/Validador.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Solicitud.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador del inicio y cierre de sesión.
 *
 *   POST /public/index.php?ruta=login    inicia la sesión con cedula y clave
 *   POST /public/index.php?ruta=logout   cierra la sesión
 *   GET  /public/index.php?ruta=sesion   devuelve los datos de la sesión actual
 *
 * Todos los errores se lanzan como excepciones con su código HTTP y las
 * responde ManejadorErrores desde el punto de entrada.
 */
class ControladorLogin
{
    /**
     * Autentica al usuario y deja la sesión iniciada.
     *
     * @return void
     * @throws Exception Si faltan datos, las credenciales son incorrectas o el usuario no tiene roles.
     */
    public function autenticar(): void
    {
        $this->verificarMetodo("POST");

        $datosEnviados = Solicitud::obtenerDatos();
        $cedula = Validador::requerido($datosEnviados["cedula"] ?? "", "cedula");
        $clave = Validador::requerido($datosEnviados["clave"] ?? "", "clave");

        $login = new Login(new AccesoDatosUsuario(Conexion::conectar()));
        $usuario = $login->autenticar($cedula, $clave);
        Conexion::desconectar();

        if ($usuario === null) {
            throw new Exception($login->getError(), 401);
        }

        if (!$usuario->tieneAlgunRol()) {
            throw new Exception(Traductor::t("login.sinRoles"), 403);
        }

        Sesion::iniciar($usuario);

        RespuestaJson::exito([
            "nombre" => $usuario->getNombre(),
            "apellido" => $usuario->getApellido(),
            "roles" => $usuario->getRoles(),
            "token" => Token::generar($usuario)
        ], Traductor::t("login.sesionIniciada"));
    }

    /**
     * Cierra la sesión del usuario autenticado.
     *
     * @return void
     * @throws Exception Si el método no es POST.
     */
    public function cerrarSesion(): void
    {
        $this->verificarMetodo("POST");
        RespuestaJson::exito(null, Traductor::t("login.sesionCerrada"));
    }

    /**
     * Devuelve los datos de la sesión actual: quién es el usuario, sus roles,
     *
     * @return void
     * @throws Exception Si el método no es GET, o el usuario de la sesión ya no existe, fue desactivado o se quedó sin roles.
     */
    public function consultarSesion(): void
    {
        $this->verificarMetodo("GET");
        Sesion::verificarSesionIniciada();

        $accesoDatosUsuario = new AccesoDatosUsuario(Conexion::conectar());
        $usuario = $accesoDatosUsuario->buscarUsuario(Sesion::cedula());
        Conexion::desconectar();

        if ($usuario === null) {
            throw new Exception(Traductor::t("login.usuarioNoEncontrado"), 401);
        }

        // Si el coordinador lo desactivó o le quitó todos los roles, la sesión abierta deja de servir.
        if (!$usuario->estaActivo()) {
            throw new Exception(Traductor::t("login.usuarioInactivo"), 401);
        }

        if (!$usuario->tieneAlgunRol()) {
            throw new Exception(Traductor::t("login.sinRoles"), 401);
        }

        // Los permisos de la API se toman de la base de datos y no del momento del login.
        Sesion::actualizarRoles($usuario);

        RespuestaJson::exito([
            "cedula" => $usuario->getCedula(),
            "nombre" => $usuario->getNombre(),
            "apellido" => $usuario->getApellido(),
            "roles" => $usuario->getRoles(),
            "idioma" => Traductor::obtenerIdioma(),
        ], Traductor::t("login.sesionActiva"));
    }

    /**
     * @param string $metodoPermitido Método HTTP que acepta la operación.
     * @return void
     * @throws Exception Si la solicitud usa otro método.
     */
    private function verificarMetodo(string $metodoPermitido): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== $metodoPermitido) {
            throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }
    }
}
