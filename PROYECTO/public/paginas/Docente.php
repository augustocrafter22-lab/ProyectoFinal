<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

require_once RUTA_NUCLEO . "/Sesion.php";
Sesion::verificarRol("docente");

require_once RUTA_CONTROLADOR . "/cargarDocente.php";