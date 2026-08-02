<?php

class ConsultaUsuario
{

private PDO $conexion;
public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }
    public function obtenerUsuarioPorCedula(string $cedula): ?Usuario
    {
        $datos = [
            '11111111' => [
                'clave' => password_hash('123456789', PASSWORD_DEFAULT),
                'activo' => true,
                'administrador' => true,
                'tecnico' => false,
                'coordinador' => false,
                'docente' => false
            ],

            '22222222' => [
                'clave' => password_hash('987654321', PASSWORD_DEFAULT),
                'activo' => true,
                'administrador' => false,
                'tecnico' => true,
                'coordinador' => false,
                'docente' => false
            ],

            '33333333' => [
                'clave' => password_hash('33333333', PASSWORD_DEFAULT),
                'activo' => true,
                'administrador' => false,
                'tecnico' => false,
                'coordinador' => true,
                'docente' => false
            ],

            '44444444' => [
                'clave' => password_hash('44444444', PASSWORD_DEFAULT),
                'activo' => true,
                'administrador' => false,
                'tecnico' => false,
                'coordinador' => false,
                'docente' => true
            ]
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
            $datos[$cedula]['coordinador'],
            $datos[$cedula]['docente']
        );
    }
}
?>