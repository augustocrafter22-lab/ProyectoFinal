<?php

class Login {
    private AccesoDatosUsuario $accesoDatosUsuario;
    private string $error = "";

    /**
     * @param AccesoDatosUsuario $accesoDatosUsuario Fuente de datos para buscar usuarios.
     */
    public function __construct(AccesoDatosUsuario $accesoDatosUsuario) {
        $this->accesoDatosUsuario = $accesoDatosUsuario;
    }

    /**
     * Valida credenciales y devuelve el usuario autenticado.
     *
     * @param string $cedula Cédula ingresada por el usuario.
     * @param string $clave Contraseña en texto plano ingresada por el usuario.
     * @return Usuario|null El usuario si las credenciales son válidas y está activo, null en caso contrario.
     */
    public function autenticar(string $cedula, string $clave): ?Usuario {
        $usuario = $this->accesoDatosUsuario->buscarUsuario($cedula);

        if ($usuario === null) {
            $this->error = "La cédula o la contraseña son incorrectas.";
            return null;
        }

        if (!$usuario->estaActivo()) {
            $this->error = "El usuario se encuentra inactivo.";
            return null;
        }

        if (!password_verify($clave, $usuario->getClaveHash())) {
            $this->error = "La cédula o la contraseña son incorrectas.";
            return null;
        }

        return $usuario;
    }

    /**
     * @return string Último mensaje de error generado por autenticar().
     */
    public function getError(): string {
        return $this->error;
    }
}
