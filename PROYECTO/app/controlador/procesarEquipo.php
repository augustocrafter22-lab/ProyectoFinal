<?php

/**
 * Controlador que procesa el alta, modificación o baja de un equipo.
 *
 * Requiere una solicitud POST con "accion" ("alta", "modificar" o "baja")
 * e "idEquipo"; para alta/modificar además requiere "idLaboratorio",
 * "marca", "estado" y "disponibilidad". Redirige al listado de equipos
 * con un mensaje de éxito o error.
 */

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AccesoDatosEquipo.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/Equipos.php");
    exit;
}

$accion = $_POST["accion"] ?? "";
$idEquipo = trim($_POST["idEquipo"] ?? "");

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosEquipo = new AccesoDatosEquipo($conexion);

    if ($accion === "baja") {
        if ($idEquipo === "") {
            throw new Exception("El equipo es obligatorio");
        }
        $accesoDatosEquipo->eliminarEquipo($idEquipo);
        $mensaje = "Equipo eliminado correctamente";
    } else {
        $idLaboratorio = trim($_POST["idLaboratorio"] ?? "");
        $marca = trim($_POST["marca"] ?? "");
        $estado = trim($_POST["estado"] ?? "");
        $disponibilidad = trim($_POST["disponibilidad"] ?? "");
        $informacion = trim($_POST["informacion"] ?? "");

        if ($idEquipo === "" || $idLaboratorio === "" || $marca === "" || $estado === "" || $disponibilidad === "") {
            throw new Exception("Complete todos los campos obligatorios");
        }

        if ($accion === "alta") {
            $accesoDatosEquipo->crearEquipo($idEquipo, $idLaboratorio, $marca, $estado, $disponibilidad, $informacion);
            $mensaje = "Equipo creado correctamente";
        } elseif ($accion === "modificar") {
            $accesoDatosEquipo->actualizarEquipo($idEquipo, $idLaboratorio, $marca, $estado, $disponibilidad, $informacion);
            $mensaje = "Equipo actualizado correctamente";
        } else {
            throw new Exception("Acción no válida");
        }
    }

    $conectorPDO->desconectar();
    header("Location: " . URL_BASE . "/public/Equipos.php?exito=" . urlencode($mensaje));
} catch (Exception $e) {
    header("Location: " . URL_BASE . "/public/Equipos.php?error=" . urlencode($e->getMessage()));
}
exit;
?>
