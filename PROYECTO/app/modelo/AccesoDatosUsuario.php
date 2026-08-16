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
                u.activo,

                CASE
                    WHEN a.cedula IS NOT NULL THEN 'coordinador'
                    ELSE NULL
                END AS rol_admin,

                CASE
                    WHEN t.cedula IS NOT NULL THEN 'tecnico'
                    ELSE NULL
                END AS rol_tecnico

            FROM USUARIO AS u

            LEFT JOIN ADMINISTRADOR AS a
                ON a.cedula = u.cedula

            LEFT JOIN TECNICO AS t
                ON t.cedula = u.cedula

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
            $datos["claveHash"],
            (bool) $datos["activo"],
            $roles
        );
    }
    public function obtenerTodos(): array {
        $sql = "
            SELECT
                u.cedula,
                u.activo,
                GROUP_CONCAT(
                    CASE 
                        WHEN a.cedula IS NOT NULL THEN 'coordinador'
                        WHEN t.cedula IS NOT NULL THEN 'tecnico'
                        WHEN d.cedula IS NOT NULL THEN 'docente'
                    END
                ) AS roles
            FROM USUARIO AS u
            LEFT JOIN ADMINISTRADOR AS a ON a.cedula = u.cedula
            LEFT JOIN TECNICO AS t ON t.cedula = u.cedula
            LEFT JOIN DOCENTE AS d ON d.cedula = u.cedula
            GROUP BY u.cedula
            ORDER BY u.cedula ASC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $usuarios = [];
        while ($datos = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $rolesArray = $datos["roles"] ? explode(",", $datos["roles"]) : [];
            $usuarios[] = [
                "cedula" => $datos["cedula"],
                "activo" => (bool) $datos["activo"],
                "roles" => $rolesArray
            ];
        }

        return $usuarios;
    }
}
?>