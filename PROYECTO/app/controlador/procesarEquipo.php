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
require_once RUTA_MODELO . "/Validador.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . URL_BASE . "/public/Equipos.php");
    exit;
}

$accion = $_POST["accion"] ?? "";

try {
    $conectorPDO = new ConectorPDO($_ENV['BD_HOST'], $_ENV['BD_USER'], $_ENV['BD_PASS'], $_ENV['BD_NAME']);
    $conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $accesoDatosEquipo = new AccesoDatosEquipo($conexion);

    if ($accion === "baja") {
        $idEquipo = Validador::requerido($_POST["idEquipo"] ?? "", "equipo");
        $accesoDatosEquipo->eliminarEquipo($idEquipo);
        $mensaje = "Equipo eliminado correctamente";
    } elseif ($accion === "alta" || $accion === "modificar") {
        $idEquipo = Validador::longitud($_POST["idEquipo"] ?? "", 1, 10, "idEquipo");
        $idLaboratorio = Validador::requerido($_POST["idLaboratorio"] ?? "", "laboratorio");
        $marca = Validador::longitud($_POST["marca"] ?? "", 1, 30, "marca");
        $estado = Validador::enLista($_POST["estado"] ?? "", ["Dañado", "Funcionando", "En mantenimiento", "No funciona"], "estado");
        $disponibilidad = Validador::enLista($_POST["disponibilidad"] ?? "", ["Disponible", "No disponible"], "disponibilidad");
        $informacion = trim($_POST["informacion"] ?? "");

        if ($accion === "alta") {
            $accesoDatosEquipo->crearEquipo($idEquipo, $idLaboratorio, $marca, $estado, $disponibilidad, $informacion);
            $mensaje = "Equipo creado correctamente";
        } else {
            $accesoDatosEquipo->actualizarEquipo($idEquipo, $idLaboratorio, $marca, $estado, $disponibilidad, $informacion);
            $mensaje = "Equipo actualizado correctamente";
        }
    } else {
        throw new Exception("Acción no válida.", 400);
    }

    $conectorPDO->desconectar();
    header("Location: " . URL_BASE . "/public/Equipos.php?exito=" . urlencode($mensaje));
} catch (PDOException $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/Equipos.php?error=" . urlencode("Ocurrió un error, intente nuevamente."));
} catch (Exception $e) {
    RegistradorErrores::registrar($e);
    header("Location: " . URL_BASE . "/public/Equipos.php?error=" . urlencode($e->getMessage()));
}
exit;
?>
