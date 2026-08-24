<?php

class Usuario {
    private string $cedula;
    private string $nombre;
    private string $apellido;
    private string $claveHash;
    private bool $activo;
    private array $roles;

    /**
     * Crea una nueva instancia de Usuario con sus datos y roles.
     *
     * @param string $cedula Cédula del usuario.
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param string $claveHash Hash de la contraseña del usuario.
     * @param bool $activo Indica si el usuario está activo.
     * @param array $roles Lista de roles asignados al usuario.
     */
    public function __construct(string $cedula, string $nombre, string $apellido, string $claveHash, bool $activo, array $roles) {
        $this->cedula = $cedula;
        $this->claveHash = $claveHash;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->activo = $activo;
        $this->roles = $roles;
    }

    /**
     * Obtiene la cédula del usuario.
     *
     * @return string Cédula del usuario.
     */
    public function getCedula(): string {
        return $this->cedula;
    }

    /**
     * Obtiene el hash de la contraseña del usuario.
     *
     * @return string Hash de la contraseña del usuario.
     */
    public function getClaveHash(): string {
        return $this->claveHash;
    }

    /**
     * Obtiene el nombre del usuario.
     *
     * @return string Nombre del usuario.
     */
    public function getNombre(): string {
        return $this->nombre;
    }

    /**
     * Obtiene el apellido del usuario.
     *
     * @return string Apellido del usuario.
     */
    public function getApellido(): string {
        return $this->apellido;
    }

    /**
     * Indica si el usuario se encuentra activo.
     *
     * @return bool true si el usuario está activo, false en caso contrario.
     */
    public function estaActivo(): bool {
        return $this->activo;
    }

    /**
     * Obtiene la lista de roles asignados al usuario.
     *
     * @return array Roles del usuario.
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * Indica si el usuario tiene el rol de coordinador.
     *
     * @return bool true si el usuario es coordinador, false en caso contrario.
     */
    public function esCoordinador(): bool {
        return in_array("coordinador", $this->roles, true);
    }

    /**
     * Indica si el usuario tiene el rol de técnico.
     *
     * @return bool true si el usuario es técnico, false en caso contrario.
     */
    public function esTecnico(): bool {
        return in_array("tecnico", $this->roles, true);
    }

    /**
     * Indica si el usuario tiene el rol de docente.
     *
     * @return bool true si el usuario es docente, false en caso contrario.
     */
    public function esDocente(): bool {
        return in_array("docente", $this->roles, true);
    }

    /**
     * Indica si el usuario tiene al menos un rol asignado.
     *
     * @return bool true si el usuario tiene algún rol, false en caso contrario.
     */
    public function tieneAlgunRol(): bool {
        return count($this->roles) > 0;
    }
}
?>