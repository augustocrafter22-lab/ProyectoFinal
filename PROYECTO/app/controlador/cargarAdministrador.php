<?php
    require_once RUTA_MODELO . "/ConectorPDO.php";
    require_once RUTA_MODELO . "/AccesoDatosUsuario.php";

    $conectorPDO = new ConectorPDO ("localhost:3306", "proyecto", "123", "test");
    $conexion = $conectorPDO->establecerConexion();

    require_once __DIR__ . "/../vista/administrador.php";

    ?>