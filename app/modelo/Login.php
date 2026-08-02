<?php

class Login
{
    private AccesoDatosUsuario $accesoDatosUsuario;

    public function __construct(AccesoDatosUsuario $accesoDatosUsuario)
    {
        $this->accesoDatosUsuario = $accesoDatosUsuario;
    }

    public function autenticar(string $cedula, string $contraseña): ?Usuario
    {
        $usuario = $this->accesoDatosUsuario
            ->obtenerUsuarioPorCedula($cedula);

        if ($usuario === null) {
            return null;
        }

        if (!$usuario->estaActivo()) {
            return null;
        }

        if (password_verify($contraseña, $usuario->getContraseña())) {
            return $usuario;
        }

        return null;
    }
}
?>