<?php

class ConectorPDO {
    private string $servername;
    private string $username;
    private string $password;
    private string $dbname;
    private ?PDO $conexion;

    /**
     * Inicializa los datos de configuración necesarios para conectarse a la base de datos.
     *
     * @param string $servername Nombre o dirección del servidor de la base de datos.
     * @param string $username Usuario para autenticarse en la base de datos.
     * @param string $password Contraseña del usuario de la base de datos.
     * @param string $dbname Nombre de la base de datos a la que se conectará.
     * @return void
     */
    public function __construct(string $servername, string $username, string $password, string $dbname) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->conexion = null;
    }

    /**
     * Establece la conexión con la base de datos utilizando los datos configurados.
     *
     * @return PDO|null La instancia de conexión PDO si la conexión fue exitosa, null si ocurrió un error.
     */
    public function establecerConexion(): ?PDO {
        try {
            $dsn = "mysql:host=" . $this->servername . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $this->conexion = new PDO($dsn, $this->username, $this->password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conexion;
        } catch (PDOException $e) {
            echo "Error al conectar: " . $e->getMessage();
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

//Código para depuración
//$ConectorPDO = new ConectorPDO ("localhost", "deklan", "123", "test");
//$ConectorPDO->establecerConexion();

?>