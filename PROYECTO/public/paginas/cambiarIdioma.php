<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

$idioma = $_GET["idioma"] ?? "es";

if ($idioma !== "es" && $idioma !== "en") {
    $idioma = "es";
}

$_SESSION["idioma"] = $idioma;

$regreso = $_SERVER["HTTP_REFERER"] ?? (URL_BASE . "/public/paginas/Login.php");
header("Location: " . $regreso);
exit;

?>
