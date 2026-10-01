<?php

require_once __DIR__ . "/../config/config.php";
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSolicitudLaboratorio.php";
require_once RUTA_MODELO . "/Validador.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: " . URL_BASE . "/public/Login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/SolicitudLaboratorio.php?error=" . urlencode("Método no permitido"));
    exit;
}

try {
    $idLaboratorio = Validador::requerido($_POST["idLaboratorio"] ?? "", "laboratorio");
    $solicitaSoftware = ($_POST["solicitaSoftware"] ?? "No") === "Si";
    $detalle = trim($_POST["detalle"] ?? "");
    $restricciones = trim($_POST["restricciones"] ?? "");
    $fechaEstimada = Validador::requerido($_POST["fechaEstimada"] ?? "", "fecha estimada");
    $horaEstimada = Validador::requerido($_POST["horaEstimada"] ?? "", "hora estimada");

    if ($solicitaSoftware) {
        $detalle = Validador::requerido($detalle, "detalle del software");
    }

    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosSolicitudLaboratorio = new AccesoDatosSolicitudLaboratorio($conexion);

    $resultado = $accesoDatosSolicitudLaboratorio->crearSolicitud(
        $idLaboratorio,
        $_SESSION["cedula"],
        $solicitaSoftware,
        $detalle !== "" ? $detalle : null,
        $restricciones !== "" ? $restricciones : null,
        $fechaEstimada,
        $horaEstimada
    );

    $conectorPDO->desconectar();

    if ($resultado) {
        header("Location: " . URL_BASE . "/public/SolicitarLab.php?exito=" . urlencode("Solicitud enviada correctamente"));
    } else {
        header("Location: " . URL_BASE . "/public/SolicitarLab.php?error=" . urlencode("Error al enviar la solicitud"));
    }

} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/SolicitarLab.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/SolicitarLab.php?error=" . urlencode($e->getMessage()));
}
exit;
?>