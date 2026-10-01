<?php

/**
 * Verifica que haya una sesión iniciada y, opcionalmente, que el usuario
 * tenga el rol indicado. Si alguna condición falla, redirige a Login.php
 * con un mensaje de error y finaliza la ejecución.
 */
function verificarSesion(?string $rol = null): void
{
    if (!isset($_SESSION["cedula"])) {
        $mensaje = "Debe iniciar sesión para acceder a esa página.";
        header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
        exit;
    }

    if ($rol !== null && ($_SESSION[$rol] ?? false) !== true) {
        $mensaje = "No tiene autorización para acceder a ese panel.";
        header("Location: " . URL_BASE . "/public/Login.php?error=" . urlencode($mensaje));
        exit;
    }
}

?>
