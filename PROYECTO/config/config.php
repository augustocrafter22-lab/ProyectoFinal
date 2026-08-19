<?php

define("RUTA_RAIZ", dirname(__DIR__));

define("RUTA_APP", RUTA_RAIZ . "/app");
define("RUTA_MODELO", RUTA_APP . "/modelo");
define("RUTA_CONTROLADOR", RUTA_APP . "/controlador");
define("RUTA_VISTA", RUTA_APP . "/vista");

define("RUTA_PUBLIC", RUTA_RAIZ . "/public");
define("URL_BASE", "/PROYECTO");

require_once RUTA_RAIZ . "/vendor/autoload.php";

//Genera una estructura que se almacenará en la memoria estática para guardar las variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(RUTA_RAIZ);
//Carga las variables de entorno provenientes de .env, si hay errores retornará excepciones (InvalidPathException, InvalidEncodingException, InvalidFileException)
$dotenv->load();


//var_dump(RUTA_RAIZ);
//var_dump(RUTA_CONTROLADOR);
//exit;
?>