<?php

class Login {
    private AccesoDatosUsuario $accesoDatosUsuario;
    private string $error = "";

    public function __construct(AccesoDatosUsuario $accesoDatosUsuario) {
        $this->accesoDatosUsuario = $accesoDatosUsuario;
    }

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

    public function getError(): string {
        return $this->error;
    }
}

 $mensaje = $_GET["error"] ?? "";
          ?>
            <p id="errorMessage" style="color: red"><?= htmlspecialchars($mensaje) ?></p>
