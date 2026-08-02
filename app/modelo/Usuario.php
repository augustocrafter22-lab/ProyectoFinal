<?php

class Usuario {
    private string $cedula;
    private string $clave;
    private bool $activo;
    private bool $administrador;
    private bool $tecnico;
    private bool $docente;
    

    public function __construct(string $cedula, string $clave, bool $activo, bool $administrador, bool $tecnico,bool $coordinador, bool $docente) {
        $this->cedula = $cedula;
        $this->clave = $clave;
        $this->activo = $activo;
        $this->administrador = $administrador;
        $this->tecnico = $tecnico;
        $this->docente = $docente;
    }
    public function getCedula(): string {
        return $this->cedula;
    }
    public function getContraseña(): string {
        return $this->clave;
    }
    public function estaActivo(): bool {
        return $this->activo;
    }
    public function esAdministrador(): bool {
        return $this->administrador;
    }
    public function esTecnico(): bool {
        return $this->tecnico;
    }
    public function esDocente(): bool {
        return $this->docente;
    }
}