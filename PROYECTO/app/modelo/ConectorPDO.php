<?php

require_once RUTA_NUCLEO . "/RegistradorErrores.php";

class ConectorPDO {
    private string $servername;
    private string $username;
    private string $password;
    private string $dbname;
    private string $port;
    private ?PDO $conexion;

    /**
     * Inicializa los datos de configuración necesarios para conectarse a la base de datos.
     *
     * @param string $servername Nombre o dirección del servidor de la base de datos.
     * @param string $username Usuario para autenticarse en la base de datos.
     * @param string $password Contraseña del usuario de la base de datos.
     * @param string $dbname Nombre de la base de datos a la que se conectará.
     * @param string $port Puerto en el que escucha la base de datos (3306 si no se indica otro).
     * @return void
     */
    public function __construct(string $servername, string $username, string $password, string $dbname, string $port = "3306") {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->conexion = null;
    }

    /**
     * Establece la conexión con la base de datos utilizando los datos configurados.
     *
     * @return PDO|null La instancia de conexión PDO si la conexión fue exitosa, null si ocurrió un error.
     */
    public function establecerConexion(): ?PDO {
        try {
            $dsn = "mysql:host=" . $this->servername . ";port=" . $this->port . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $this->conexion = new PDO($dsn, $this->username, $this->password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conexion;
        } catch (PDOException $e) {
            RegistradorErrores::registrar($e);
            return null;
        }
    }

    /**
     * Cierra la conexión activa con la base de datos.
     *
     * @return void
     */
    public function desconectar() {
        $this->conexion = null;
    }
}
