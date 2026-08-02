<?php

class AccesoDatosUsuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        // guarda la conexión recibida desde procesarLogin.php.
        $this->conexion = $conexion;
    }

    public function obtenerUsuarioPorCedula(string $cedula): ?Usuario
    {
        // consulta el usuario y comprueba en qué tablas de roles aparece.
        $sql = "
            SELECT
                u.cedula,
                u.clave,
                u.activo,
                a.cedula AS administrador,
                t.cedula AS tecnico,
                d.cedula AS docente
            FROM USUARIO AS u

            LEFT JOIN ADMINISTRADOR AS a
                ON u.cedula = a.cedula

            LEFT JOIN TECNICO AS t
                ON u.cedula = t.cedula

            LEFT JOIN DOCENTE AS d
                ON u.cedula = d.cedula

            WHERE u.cedula = :cedula
        ";

        // prepara la consulta SQL.
        $consulta = $this->conexion->prepare($sql);

        // sustituye :cedula por la cédula recibida y ejecuta la consulta.
        $consulta->execute([
            "cedula" => $cedula
        ]);

        // recupera el usuario encontrado como un arreglo asociativo.
        $datosUsuario = $consulta->fetch(PDO::FETCH_ASSOC);

        // si la consulta no encontró al usuario, devuelve null.
        if ($datosUsuario === false) {
            return null;
        }

        // comprueba los roles según si la cédula apareció en cada tabla.
        $esAdministrador = $datosUsuario["administrador"] !== null;
        $esTecnico = $datosUsuario["tecnico"] !== null;
        $esDocente = $datosUsuario["docente"] !== null;

        // construye y devuelve el objeto Usuario.
        return new Usuario(
            $datosUsuario["cedula"],
            $datosUsuario["clave"],
            (bool) $datosUsuario["activo"],
            $esAdministrador,
            $esTecnico,
            $esDocente
        );
    }
}

?>