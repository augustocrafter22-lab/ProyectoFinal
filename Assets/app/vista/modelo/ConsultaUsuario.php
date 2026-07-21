<?php

require_once __DIR__ . "/Usuario.php";

class ConsultaUsuario {
    private array $usuarios;

    public function __construct() {
        $this->usuarios = [
            [
                "cedula"    => "11111111",
                "claveHash" => password_hash("coordinador123", PASSWORD_DEFAULT),
                "activo"    => true,
                "rol"       => "coordinador"
            ],
            [
                "cedula"    => "22222222",
                "claveHash" => password_hash("tecnico123", PASSWORD_DEFAULT),
                "activo"    => true,
                "rol"       => "tecnico"
            ],
            [
                "cedula"    => "33333333",
                "claveHash" => password_hash("docente123", PASSWORD_DEFAULT),
                "activo"    => true,
                "rol"       => "docente"
            ]
        ];
    }

    
    public function buscarUsuario(string $cedula): ?Usuario {
        foreach ($this->usuarios as $datos) {
            if ($datos["cedula"] === $cedula) {
                return new Usuario(
                    $datos["cedula"],
                    $datos["claveHash"],
                    $datos["activo"],
                    $datos["rol"]
                );
            }
        }

        return null;
    }
}

?>