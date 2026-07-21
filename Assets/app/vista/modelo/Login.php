<?php

class Login {
    private ConsultaUsuario $consultaUsuario;

    public function __construct(ConsultaUsuario $consultaUsuario) {
        $this->consultaUsuario = $consultaUsuario;
    }

    public function autenticar(string $cedula, string $clave): ?Usuario {
        $usuario = $this->consultaUsuario->buscarUsuario($cedula);

        if ($usuario === null) {
            return null;
        }

        if (!$usuario->estaActivo()) {
            return null;
        }

        if (!password_verify($clave, $usuario->getClaveHash())) {
            return null;
        }

        return $usuario;
    }
}

?>