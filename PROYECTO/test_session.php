<?php

require_once __DIR__ . "/config/config.php";

// Simular una sesión logueada
session_start();
session_destroy(); // Limpiar sesión anterior

session_start();
session_regenerate_id(true);

// Crear sesión de prueba con rol coordinador
$_SESSION["cedula"] = "12345678";
$_SESSION["coordinador"] = true;
$_SESSION["tecnico"] = false;
$_SESSION["roles"] = ["coordinador"];

echo "Sesión creada<br>";
echo "Cédula: " . $_SESSION["cedula"] . "<br>";
echo "Coordinador: " . ($_SESSION["coordinador"] ? "Sí" : "No") . "<br>";
echo "Técnico: " . ($_SESSION["tecnico"] ? "Sí" : "No") . "<br>";
echo "<br>";
echo "<a href='" . URL_BASE . "/app/vista/administrador.php'>Ir a administrador.php</a>";

?>