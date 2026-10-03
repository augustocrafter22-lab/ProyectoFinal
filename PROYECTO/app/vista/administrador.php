<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
    <title>Administrador de usuarios</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/Editor.css">
</head>

<body data-cedula-sesion="<?= htmlspecialchars($_SESSION["cedula"]) ?>">
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
                <li><a href="<?= URL_BASE ?>/public/Administrador.php"><?= Traductor::t("common.inicio") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("administrador.bienvenida") ?></h1>
        <p><?= Traductor::t("administrador.subtitulo") ?></p>
    </section>

    <p id="mensajeAdministrador" role="status"></p>

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
        <tbody id="cuerpoTablaUsuarios"></tbody>
    </table>

    <section class="botonera">
        <button class="boton-principal" id="btnCrear" type="button"><?= Traductor::t("administrador.btnAgregarUsuario") ?></button>
    </section>


    <dialog class="dialogGestionarUsuario" id="dialogGestionarUsuario">
        <button id="btnCerrarGestionarUsuario" type="button">
            <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="Cerrar" width="24" height="24">
        </button>
        <form id="formularioGestionarUsuario">
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
                        <input type="password" id="contrasenia" name="contrasenia" autocomplete="new-password" placeholder="<?= Traductor::t("administrador.placeholderContrasenia") ?>">
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
                <p id="mensajeDialogoUsuario" role="alert"></p>
                <button class="boton-secundario" type="submit" id="btnGuardarUsuario"><?= Traductor::t("administrador.btnGuardarUsuario") ?></button>
            </fieldset>
        </form>
    </dialog>

    <?php
    // Textos que usa administrador.js, ya traducidos al idioma de la sesión.
    $textosAdministrador = [
        "roles" => [
            "coordinador" => Traductor::t("common.coordinador"),
            "tecnico" => Traductor::t("common.tecnico"),
            "docente" => Traductor::t("common.docente")
        ],
        "activo" => Traductor::t("administrador.activo"),
        "inactivo" => Traductor::t("administrador.inactivo"),
        "btnEditar" => Traductor::t("administrador.btnEditar"),
        "btnActivar" => Traductor::t("administrador.btnActivar"),
        "btnDesactivar" => Traductor::t("administrador.btnDesactivar"),
        "btnEliminar" => Traductor::t("administrador.btnEliminar"),
        "placeholderContrasenia" => Traductor::t("administrador.placeholderContrasenia"),
        "placeholderContraseniaEditar" => Traductor::t("administrador.placeholderContraseniaEditar"),
        "cargando" => Traductor::t("administrador.cargando"),
        "sinUsuarios" => Traductor::t("administrador.sinUsuarios"),
        "errorConexion" => Traductor::t("administrador.errorConexion"),
        "confirmarActivar" => Traductor::t("administrador.confirmarActivar"),
        "confirmarDesactivar" => Traductor::t("administrador.confirmarDesactivar"),
        "confirmarEliminar" => Traductor::t("administrador.confirmarEliminar"),
        "exitoCreado" => Traductor::t("administrador.exitoCreado"),
        "exitoActualizado" => Traductor::t("administrador.exitoActualizado"),
        "exitoActivado" => Traductor::t("administrador.exitoActivado"),
        "exitoDesactivado" => Traductor::t("administrador.exitoDesactivado"),
        "exitoEliminado" => Traductor::t("administrador.exitoEliminado")
    ];
    ?>
    <script type="application/json" id="textosAdministrador"><?= json_encode($textosAdministrador, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/administrador.js"></script>
</body>

</html>