<?php

/**
 * Controlador que carga la vista para registrar un diagnóstico.
 *
 * Obtiene el listado de tickets disponibles para que el técnico
 * seleccione a cuál asociar el nuevo diagnóstico.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $tickets = $accesoDatosTicket->listarTickets();

    $conectorPDO->desconectar();
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    echo "Ocurrió un error, intente nuevamente.";
    exit;
}

require_once RUTA_VISTA . "/registrarDiagnostico.php";

?>