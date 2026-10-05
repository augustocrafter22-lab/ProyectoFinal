<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

require_once RUTA_NUCLEO . "/Sesion.php";
Sesion::verificarRol("tecnico");

require_once RUTA_CONTROLADOR . "/cargarVistaLab.php";

?>