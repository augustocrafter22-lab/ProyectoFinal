<?php

class ConsultaUsuario
{
    public function buscarUsuario(string $cedula): ?Usuario
    {
        $datos = [
            '12345678' => ['clave' => password_hash('123456789', PASSWORD_DEFAULT), 'activo' => true, 'administrador' => true, 'tecnico' => false, 'coordinador' => false],
            '87654321' => ['clave' => password_hash('987654321', PASSWORD_DEFAULT), 'activo' => true, 'administrador' => false, 'tecnico' => true, 'coordinador' => false],
        ];

        if (!isset($datos[$cedula])) {
            return null;
        }

        return new Usuario(
            $cedula,
            $datos[$cedula]['clave'],
            $datos[$cedula]['activo'],
            $datos[$cedula]['administrador'],
            $datos[$cedula]['tecnico'],
            $datos[$cedula]['coordinador']
        );
    }
}
?>