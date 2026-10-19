<?php

require_once RUTA_MODELO . "/AccesoDatosDashboard.php";
require_once RUTA_VISTA . "/RespuestaJson.php";
require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Traductor.php";

/**
 * Controlador del dashboard de tickets.
 *
 *   GET /public/index.php?ruta=dashboard   métricas de los tickets del sistema
 *
 * Lo comparten el coordinador y el técnico.
 */
class ControladorDashboard
{
    /**
     * Devuelve el total de tickets, la cantidad por estado, los tiempos de
     * resolución y las incidencias por salón.
     *
     * @return void
     * @throws Exception Si el método no es GET o si falla la base de datos.
     */
    public function gestionar(): void
    {
        Sesion::verificarRolApi(["coordinador", "tecnico"]);

        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            throw new Exception(Traductor::t("comun.errorMetodoNoPermitido"), 405);
        }

        $accesoDatosDashboard = new AccesoDatosDashboard(Conexion::conectar());

        $metricas = [
            "totalReportes" => $accesoDatosDashboard->contarTotal(),
            "porEstado" => $accesoDatosDashboard->contarPorEstado(),
            "tiemposResolucion" => $accesoDatosDashboard->obtenerTiemposResolucion(),
            "incidenciasPorSalon" => $accesoDatosDashboard->obtenerIncidenciasPorSalon()
        ];

        Conexion::desconectar();

        RespuestaJson::exito($metricas, Traductor::t("dashboard.metricas"));
    }
}
