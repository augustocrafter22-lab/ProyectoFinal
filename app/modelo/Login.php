<?php
class Login {
private ConsultaUsuario $consultaUsuario;

public function __construct() {
    $this->consultaUsuario = new ConsultaUsuario();
}

public function autenticar(string $cedula, string $contraseña): ?Usuario {
    $usuario = $this->consultaUsuario->obtenerUsuarioPorCedula($cedula);
    if ($usuario === null) {
        return null;
    }
    if (!$usuario->estaActivo()) {
        return null;
    }
    if ( password_verify($contraseña, $usuario->getContraseña()) ) {
        return $usuario;
    }
    return null;
}
}
?>