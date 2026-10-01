<?php

/**
 * Controlador que actualiza estado y prioridad de un ticket.
 *
 * Requiere sesión de técnico activa y una solicitud POST con "idTicket",
 * "estado" (uno de Pendiente/En Proceso/Resuelto/Cerrado) y "prioridad"
 * (uno de Indefinida/Alta/Media/Baja). Responde con un JSON indicando
 * éxito o el error correspondiente.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosTicket.php";
require_once RUTA_MODELO . "/Validador.php";

header("Content-Type: application/json");

if (!isset($_SESSION["cedula"]) || !isset($_SESSION["tecnico"]) || $_SESSION["tecnico"] !== true) {
    http_response_code(403);
    echo json_encode(["exito" => false, "mensaje" => "No tiene autorización para realizar esta acción."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
    exit;
}

try {
    $idTicket = Validador::requerido($_POST["idTicket"] ?? "", "ticket");
    $estado = Validador::enLista($_POST["estado"] ?? "", ["Pendiente", "En Proceso", "Resuelto", "Cerrado"], "estado");
    $prioridad = Validador::enLista($_POST["prioridad"] ?? "", ["Indefinida", "Alta", "Media", "Baja"], "prioridad");

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosTicket = new AccesoDatosTicket($conexion);
    $accesoDatosTicket->actualizarEstadoYPrioridad($idTicket, $estado, $prioridad);

    $conectorPDO->desconectar();

    echo json_encode(["exito" => true, "mensaje" => "Ticket actualizado correctamente."]);

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    http_response_code(500);
    echo json_encode(["exito" => false, "mensaje" => "Ocurrió un error, intente nuevamente."]);
} catch (Exception $e) {
    $codigo = $e->getCode() >= 400 && $e->getCode() <= 599 ? (int) $e->getCode() : 500;
    http_response_code($codigo);

    if ($codigo === 500) {
        RegistradorErrores::registrar($e);
        echo json_encode(["exito" => false, "mensaje" => "Ocurrió un error, intente nuevamente."]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
    }
}

?>
