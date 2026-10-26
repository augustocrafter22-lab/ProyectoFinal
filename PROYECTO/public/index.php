<?php

require_once __DIR__ . "/../config/config.php";
require_once RUTA_NUCLEO . "/ManejadorErrores.php";

// Sin ruta no hay nada que responder: se muestra la pantalla de login.
if (!isset($_GET["ruta"])) {
    header("Location: ./paginas/login.html");
    exit;
}

try {
    Traductor::iniciar();

    // Captura el nombre del recurso que viene luego de ? en la URL
    $ruta = $_GET["ruta"];

    if (!is_string($ruta) || trim($ruta) === "") {
        throw new Exception(Traductor::t("comun.errorRutaInvalida"), 400);
    }

    require RUTA_RUTAS . "/api.php";
} catch (Throwable $error) {
    // Todas las excepciones del sistema terminan acá, en un único lugar.
    ManejadorErrores::responder($error);
}
