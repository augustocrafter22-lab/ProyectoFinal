<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();

require_once RUTA_VISTA . "/login.php";

?>
