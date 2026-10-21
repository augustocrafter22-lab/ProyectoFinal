<?php

require_once RUTA_MODELO . "/ConectorPDO.php";

/**
 * Abre y cierra la conexión con la base de datos para toda la aplicación,
 * así los controladores no repiten los datos de conexión ni la verificación.
 */
class Conexion
{
    private static ?ConectorPDO $conectorPDO = null;

    /**
     * Abre la conexión con los datos del archivo .env.
     *
     * @return PDO Conexión lista para crear un DAO.
     * @throws Exception Si no se pudo conectar a la base de datos.
     */
    public static function conectar(): PDO
    {
        // Si el .env no define BD_PORT se usa el puerto por defecto de MariaDB.
        $puerto = $_ENV["BD_PORT"] ?? "3306";
        self::$conectorPDO = new ConectorPDO($_ENV["BD_HOST"], $_ENV["BD_USER"], $_ENV["BD_PASS"], $_ENV["BD_NAME"], $puerto);
        $conexion = self::$conectorPDO->establecerConexion();

        if ($conexion === null) {
            throw new Exception("No se pudo conectar a la base de datos.", 500);
        }

        return $conexion;
    }

    /**
     * Cierra la conexión abierta con conectar(), si existe.
     *
     * @return void
     */
    public static function desconectar(): void
    {
        if (self::$conectorPDO !== null) {
            self::$conectorPDO->desconectar();
            self::$conectorPDO = null;
        }
    }
}
