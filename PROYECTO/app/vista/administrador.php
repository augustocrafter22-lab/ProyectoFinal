<?php

require_once __DIR__ . "/../../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    header("Location: login.php");
    exit;
}

require_once RUTA_CONTROLADOR . "/cargarAdministrador.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de usuarios</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/Editor.css">
</head>

<body>
    <header class="BarraNavegacion">

        <nav>
            <button class="btnMenu" id="btnMenu" type="button"><img class="menu"
                    src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40" height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40" height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="<?= URL_BASE ?>/app/vista/administrador.php">Inicio</a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1>Bienvenido, coordinador</h1>
        <p>Este es el administrador de usuarios</p>
    </section>
    
    <section class="modulo">
        <fieldset class="controles">
            <legend>Filtros</legend>
            <label for="filtroPorRol">Rol:</label>
            <select id="filtroPorRol">
                <option value="">Todos</option>
                <option value="coordinador">Coordinador</option>
                <option value="tecnico">Técnico</option>
            </select>
            <button class="boton-filtro" id="btnLimpiarFiltroUsuarios" type="button">Limpiar filtros</button>
        </fieldset>

        <table id="tablaUsuarios">
            <caption>Listado de usuarios registrados</caption>
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Roles</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTablaUsuarios">
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario["cedula"]) ?></td>
                        <td><?= htmlspecialchars(implode(", ", $usuario["roles"])) ?></td>
                        <td><?= $usuario["activo"] ? "Activo" : "Inactivo" ?></td>
                        <td>
                            <button class="btnEditar" data-cedula="<?= htmlspecialchars($usuario["cedula"]) ?>">Editar</button>
                            <button class="btnEliminar" data-cedula="<?= htmlspecialchars($usuario["cedula"]) ?>">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <button class="boton-principal" id="btnAltaUsuario" type="button">Agregar usuario</button>

    <dialog class="dialogGestionarUsuario" id="dialogGestionarUsuario">
        <button id="btnCerrarGestionarUsuario" type="button">
            <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="Cerrar" width="24" height="24">
        </button>
        <form id="formularioGestionarUsuario" method="POST">
            <fieldset>
                <legend>Gestión de usuario</legend>
                <fieldset>
                    <legend>Datos del usuario</legend>
                    <div class="cajaEntradaDeDatos">
                        <label for="ci">CI</label>
                        <input type="text" id="ci" name="ci" placeholder="Ingrese la CI"
                               inputmode="numeric" maxlength="8">
                    </div>
                    <div class="cajaEntradaDeDatos">
                        <label for="contrasenia">Contraseña</label>
                        <input type="password" id="contrasenia" name="contrasenia"
                               placeholder="Ingrese la contraseña">
                    </div>
                    <div class="cajaEntradaDeDatos">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol">
                            <option value="" disabled selected>Seleccione un rol</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="tecnico">Técnico</option>
                            <option value="docente">Docente</option>
                        </select>
                    </div>
                </fieldset>
                <button class="boton-secundario" type="submit">Guardar usuario</button>
            </fieldset>
        </form>
    </dialog>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/administrador.js"></script>               
</body>
</html>