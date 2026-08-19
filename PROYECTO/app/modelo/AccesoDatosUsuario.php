<?php

require_once __DIR__ . "/Usuario.php";

class AccesoDatosUsuario {
    private PDO $conexion;

    public function __construct(PDO $conexion) {
        $this->conexion = $conexion;
    }

    public function buscarUsuario(string $cedula): ?Usuario {
        $sql = "
            SELECT
                u.cedula,
                u.clave AS claveHash,
                u.nombre,
                u.apellido,
                u.activo,

                CASE
                    WHEN a.cedula IS NOT NULL THEN 'coordinador'
                    ELSE NULL
                END AS rol_admin,

                CASE
                    WHEN t.cedula IS NOT NULL THEN 'tecnico'
                    ELSE NULL
                END AS rol_tecnico,

                CASE
                    WHEN d.cedula IS NOT NULL THEN 'docente'
                    ELSE NULL
                END AS rol_docente

            FROM USUARIO AS u

            LEFT JOIN ADMINISTRADOR AS a
                ON a.cedula = u.cedula

            LEFT JOIN TECNICO AS t
                ON t.cedula = u.cedula

            LEFT JOIN DOCENTE AS d
                ON d.cedula = u.cedula
            
            WHERE u.cedula = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["cedula" => $cedula]);

        $datos = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($datos === false) {
            return null;
        }

        $roles = [];
        if ($datos["rol_admin"] !== null) {
            $roles[] = "coordinador";
        }
        if ($datos["rol_tecnico"] !== null) {
            $roles[] = "tecnico";
        }
        if ($datos["rol_docente"] !== null) {
            $roles[] = "docente";
        }

        return new Usuario(
            $datos["cedula"],
            $datos["nombre"],
            $datos["apellido"],
            $datos["claveHash"],
            (bool) $datos["activo"],
            $roles
        );
    }
    public function obtenerTodos(): array {
        $sql = "
            SELECT
                u.cedula,
                u.nombre,
                u.apellido,
                u.activo,
                CONCAT_WS(', ',
                    CASE WHEN a.cedula IS NOT NULL THEN 'coordinador' END,
                    CASE WHEN t.cedula IS NOT NULL THEN 'tecnico' END,
                    CASE WHEN d.cedula IS NOT NULL THEN 'docente' END
                ) AS roles
            FROM USUARIO AS u
            LEFT JOIN ADMINISTRADOR AS a ON a.cedula = u.cedula
            LEFT JOIN TECNICO AS t ON t.cedula = u.cedula
            LEFT JOIN DOCENTE AS d ON d.cedula = u.cedula
            ORDER BY u.cedula ASC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $usuarios = [];
        while ($datos = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $rolesArray = $datos["roles"] ? explode(", ", $datos["roles"]) : [];
            $usuarios[] = [
                "cedula" => $datos["cedula"],
                "nombre"=> $datos["nombre"],
                "apellido"=> $datos["apellido"],
                "activo" => (bool) $datos["activo"],
                "roles" => $rolesArray
            ];
        }

        return $usuarios;
    }
}
?>