<?php

define("RUTA_RAIZ", dirname(__DIR__));

define("RUTA_APP", RUTA_RAIZ . "/app");
define("RUTA_MODELO", RUTA_APP . "/modelo");
define("RUTA_CONTROLADOR", RUTA_APP . "/controlador");
define("RUTA_VISTA", RUTA_APP . "/vista");

define("RUTA_PUBLIC", RUTA_RAIZ . "/public");
define("URL_BASE", "/PROYECTO");

// Configuración de BD
define("BD_HOST", "localhost");
define("BD_USER", "deklan");
define("BD_PASS", "123");
define("BD_NAME", "test");

//var_dump(RUTA_RAIZ);
//var_dump(RUTA_CONTROLADOR);
//exit;
?>