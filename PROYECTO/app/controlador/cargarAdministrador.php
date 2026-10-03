<?php
 
/**
 * Controlador que carga el panel de administrador.
 *
 * El listado de usuarios y el alta, edición, activación, desactivación
 * y baja ahora se manejan con fetch contra la API (public/api/usuarios.php)
 * desde administrador.js. Acá solo se inicia el traductor y se carga la vista.
 */
 
require_once RUTA_MODELO . "/Traductor.php";
Traductor::iniciar();
 
require_once RUTA_VISTA . "/administrador.php";
?>
 