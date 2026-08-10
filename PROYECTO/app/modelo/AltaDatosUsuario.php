<?php

/**
 * Clase encargada de realizar operaciones de alta
 * relacionadas con los usuarios del sistema.
 */
class AltaDatosUsuario
{
    private PDO $conexion;

    /**
     * Constructor parametrizado que recibe una conexión a la base de datos.
     * @param PDO $conexion La conexión a la base de datos. PRECONDICIÓN: No debe ser NULL.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Registra un nuevo usuario con su rol.
     *
     * @param string $cedula Cédula del usuario.
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param string $claveHash Hash de la contraseña.
     * @param string $rol Rol del Empleado en el sistema
     *
     * @return bool TRUE si el registro se completa correctamente, FALSE en caso contrario.
     */
    public function registrarUsuario(string $cedula, string $nombre, string $apellido, string $claveHash, string $rol): bool
    {

        try {
            //Método que ejecuta de forma agrupada todas las instrucciones dirigidas a la base de datos
            //Si una instrucción falla, retorna excepción con la posibilidad de deshacer cambios con rollBack()
            $this->conexion->beginTransaction();

            $sqlUsuario = "INSERT INTO USUARIO (cedula, nombre, apellido, claveHash) VALUES (:cedula, :nombre, :apellido, :claveHash)";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);

            $consultaUsuario->execute(["cedula" => $cedula, "nombre" => $nombre, "apellido" => $apellido, "claveHash" => $claveHash]);

            switch ($rol) {
                case "Administrador":
                    $sqlRol = "INSERT INTO ADMINISTRADOR (cedula) VALUES (:cedula)";
                    break;
                case "Logística":
                    $sqlRol = "INSERT INTO LOGISTICA (cedula) VALUES (:cedula)";
                    break;
                default:
                    $this->conexion->rollBack();
                    return false;
            }
            
            $consultaRol = $this->conexion->prepare($sqlRol);

            $consultaRol->execute(["cedula" => $cedula]);

            //Confirma todas las operaciones realizadas.
            $this->conexion->commit();

            return true;

        } catch (PDOException $error) {

            //Verifica si se encuentra en una transacción
            if ($this->conexion->inTransaction()) {
                //Deshace los cambios causados por la excepción
                $this->conexion->rollBack();
            }

            return false;
        }
    }
}

?>