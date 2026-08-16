<?php

class Usuario {
    private string $cedula;
    private string $claveHash;
    private bool $activo;
    private array $roles;

    public function __construct(string $cedula, string $claveHash, bool $activo, array $roles) {
        $this->cedula = $cedula;
        $this->claveHash = $claveHash;
        $this->activo = $activo;
        $this->roles = $roles;
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

    public function getRoles(): array {
        return $this->roles;
    }

    public function esCoordinador(): bool {
        return in_array("coordinador", $this->roles, true);
    }

    public function esTecnico(): bool {
        return in_array("tecnico", $this->roles, true);
    }

    public function esDocente(): bool {
        return in_array("docente", $this->roles, true);
    }

    public function tieneAlgunRol(): bool {
        return count($this->roles) > 0;
    }
}
?>