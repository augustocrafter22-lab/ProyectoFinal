<?php

require_once __DIR__ . "/../config/config.php";

session_start();

require_once RUTA_CONTROLADOR . "/verificarSesion.php";
verificarSesion("tecnico");

require_once RUTA_CONTROLADOR . "/cargarTecnico.php";