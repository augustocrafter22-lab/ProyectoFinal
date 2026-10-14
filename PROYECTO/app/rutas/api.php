<?php

// Espacio donde se encuentran los endpoints que establecen la comunicación con el controlador.
// Cada ruta indica qué controlador la atiende y qué método de ese controlador se ejecuta.
$endpoints = [
    // Sesión e idioma
    "login" => ["ControladorLogin", "autenticar"],
    "logout" => ["ControladorLogin", "cerrarSesion"],
    "sesion" => ["ControladorLogin", "consultarSesion"],
    "idioma" => ["ControladorIdioma", "cambiarIdioma"],
    "traducciones" => ["ControladorIdioma", "obtenerTraducciones"],

    // Recursos del sistema: gestionar() decide qué hacer según el método HTTP
    "usuarios" => ["ControladorUsuario", "gestionar"],
    "laboratorios" => ["ControladorLaboratorio", "gestionar"],
    "solicitudes" => ["ControladorSolicitudLaboratorio", "gestionar"],
    "equipos" => ["ControladorEquipo", "gestionar"],
    "prestamos" => ["ControladorPrestamo", "gestionar"],
    "tickets" => ["ControladorTicket", "gestionar"],
    "diagnosticos" => ["ControladorDiagnostico", "gestionar"],
    "reparaciones" => ["ControladorReparacion", "gestionar"],
    "soluciones" => ["ControladorSolucion", "gestionar"],
    "intervenciones" => ["ControladorIntervencion", "gestionar"],
    "reemplazos" => ["ControladorReemplazo", "gestionar"],
    "historialEquipo" => ["ControladorHistorialEquipo", "gestionar"],
    "dashboard" => ["ControladorDashboard", "gestionar"]
];

if (!isset($endpoints[$ruta])) {
    throw new Exception(Traductor::t("comun.errorRutaNoEncontrada"), 404);
}

// Solo se carga el controlador de la ruta pedida.
[$nombreControlador, $metodoControlador] = $endpoints[$ruta];
require_once RUTA_CONTROLADOR . "/" . $nombreControlador . ".php";

$controlador = new $nombreControlador();
$controlador->$metodoControlador();
