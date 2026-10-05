<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

require_once RUTA_NUCLEO . "/Sesion.php";
Sesion::verificarRol("tecnico");

$modoReparacion = true;
require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/registrarReparacion.php";

?>
