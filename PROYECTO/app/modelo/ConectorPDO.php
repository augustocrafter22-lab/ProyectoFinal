<?php

class ConectorPDO {
    private string $servername;
    private string $username;
    private string $password;
    private string $dbname;
    private ?PDO $conexion;

    public function __construct(string $servername, string $username, string $password, string $dbname) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->conexion = null;
    }

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

    public function desconectar() {
        $this->conexion = null;
    }
}

//Código para depuración
//$ConectorPDO = new ConectorPDO ("localhost", "deklan", "123", "test");
//$ConectorPDO->establecerConexion();

?>