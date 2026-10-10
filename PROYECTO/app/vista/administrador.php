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
                    src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40"
                    height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40"
                    height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="<?= URL_BASE ?>/public/paginas/Administrador.php"><?= Traductor::t("common.inicio") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/Dashboard.php"><?= Traductor::t("nav.dashboard") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("administrador.bienvenida") ?></h1>
        <p><?= Traductor::t("administrador.subtitulo") ?></p>
    </section>

    <?php if (isset($_GET["exito"])): ?>
        <p id="mensajeExitoAdministrador" role="status" style="color: green"><?= htmlspecialchars($_GET["exito"]) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET["error"])): ?>
        <p id="mensajeErrorAdministrador" role="status" style="color: red"><?= htmlspecialchars($_GET["error"]) ?></p>
    <?php endif; ?>

    <table id="tablaUsuarios">
        <caption><?= Traductor::t("administrador.captionTabla") ?></caption>
        <thead>
            <tr>
                <th><?= Traductor::t("administrador.thCi") ?></th>
                <th><?= Traductor::t("administrador.thNombre") ?></th>
                <th><?= Traductor::t("administrador.thApellido") ?></th>
                <th><?= Traductor::t("administrador.thRoles") ?></th>
                <th><?= Traductor::t("administrador.thEstado") ?></th>
                <th><?= Traductor::t("administrador.thAcciones") ?></th>
            </tr>
        </thead>
        <tbody id="cuerpoTablaUsuarios">
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario["cedula"]) ?></td>
                    <td><?= htmlspecialchars($usuario["nombre"]) ?></td>
                    <td><?= htmlspecialchars($usuario["apellido"]) ?></td>
                    <td><?= htmlspecialchars(implode(", ", $usuario["roles"])) ?></td>
                    <td><?= $usuario["activo"] ? Traductor::t("administrador.activo") : Traductor::t("administrador.inactivo") ?></td>
                    <td>
                        <div class="Operaciones">
                            <button type="button" class="btnEditar"><?= Traductor::t("administrador.btnEditar") ?></button>

                            <?php if ($usuario["activo"]): ?>
                                <form action="procesarDesactivarUsuario.php" method="POST" class="formularioDesactivarUsuario">
                                    <input type="hidden" name="cedula" value="<?= htmlspecialchars($usuario["cedula"]) ?>">
                                    <button type="submit" class="btnEliminar"><?= Traductor::t("administrador.btnDesactivar") ?></button>
                                </form>
                            <?php else: ?>
                                <form action="procesarActivarUsuario.php" method="POST" class="formularioActivarUsuario">
                                    <input type="hidden" name="cedula" value="<?= htmlspecialchars($usuario["cedula"]) ?>">
                                    <button type="submit" class="btnActivar"><?= Traductor::t("administrador.btnActivar") ?></button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <section class="botonera">
        <button class="boton-principal" id="btnCrear" type="button"><?= Traductor::t("administrador.btnAgregarUsuario") ?></button>
    </section>


    <dialog class="dialogGestionarUsuario" id="dialogGestionarUsuario">
        <button id="btnCerrarGestionarUsuario" type="button">
            <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="Cerrar" width="24" height="24">
        </button>
        <form id="formularioGestionarUsuario" method="POST">
            <fieldset>
                <legend><?= Traductor::t("administrador.dialogTitulo") ?></legend>
                <fieldset>
                    <legend><?= Traductor::t("administrador.dialogSubtitulo") ?></legend>
                    <div class="cajaEntradaDeDatos">
                        <label for="ci"><?= Traductor::t("administrador.labelCi") ?></label>
                        <input type="text" id="ci" name="ci" placeholder="<?= Traductor::t("administrador.placeholderCi") ?>" inputmode="numeric"
                            maxlength="8">
                    </div>
                    <div class="cajaEntradaDeDatos">
                        <label for="nombre"><?= Traductor::t("administrador.labelNombre") ?></label>
                        <input type="text" id="nombre" name="nombre" placeholder="<?= Traductor::t("administrador.placeholderNombre") ?>">
                    </div>
                    <div class="cajaEntradaDeDatos">
                        <label for="apellido"><?= Traductor::t("administrador.labelApellido") ?></label>
                        <input type="text" id="apellido" name="apellido" placeholder="<?= Traductor::t("administrador.placeholderApellido") ?>">
                    </div>
                    <div class="cajaEntradaDeDatos">
                        <label for="contrasenia"><?= Traductor::t("administrador.labelContrasenia") ?></label>
                        <input type="password" id="contrasenia" name="contrasenia" placeholder="<?= Traductor::t("administrador.placeholderContrasenia") ?>">
                    </div>
                    <fieldset class="roles">
                        <legend><?= Traductor::t("administrador.legendRoles") ?></legend>
                        <label>
                            <input type="checkbox" name="roles[]" value="coordinador"> <?= Traductor::t("common.coordinador") ?>
                        </label>
                        <label>
                            <input type="checkbox" name="roles[]" value="tecnico"> <?= Traductor::t("common.tecnico") ?>
                        </label>
                        <label>
                            <input type="checkbox" name="roles[]" value="docente"> <?= Traductor::t("common.docente") ?>
                        </label>
                    </fieldset>
                </fieldset>
                <button class="boton-secundario" type="submit"><?= Traductor::t("administrador.btnGuardarUsuario") ?></button>
            </fieldset>
        </form>
    </dialog>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/administrador.js"></script>
</body>

</html>