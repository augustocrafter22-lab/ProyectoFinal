<?php

class Usuario {
    private string $cedula;
    private string $claveHash;
    private bool $activo;
    private string $rol; 

    public function __construct(string $cedula, string $claveHash, bool $activo, string $rol) {
        $this->cedula = $cedula;
        $this->claveHash = $claveHash;
        $this->activo = $activo;
        $this->rol = $rol;
    }

    public function getCedula(): string {
        return $this->cedula;
    }

    public function getClaveHash(): string {
        return $this->claveHash;
    }

    public function estaActivo(): bool {
        return $this->activo;
    }

    public function getRol(): string {
        return $this->rol;
    }

    public function esCoordinador(): bool {
        return $this->rol === "coordinador";
    }

    public function esTecnico(): bool {
        return $this->rol === "tecnico";
    }

    public function esSolicitante(): bool {
        return $this->rol === "solicitante";
    }
}

?>