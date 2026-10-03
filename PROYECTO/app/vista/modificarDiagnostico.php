<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Diagnóstico</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
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
                <li><a href="Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="ConsultarDiagnostico.php"><?= Traductor::t("nav.verDiagnostico") ?></a></li>
                <li><a class="cerrarSesion" href="cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <header class="encabezado">
        <h1><?= Traductor::t("modificarDiagnostico.titulo") ?></h1>
        <p><?= Traductor::t("modificarDiagnostico.subtitulo") ?></p>
    </header>

    <p id="mensajeModificarDiagnostico" role="status"></p>

    <section class="modulo" id="modificarDiagnostico">
        <h2><?= Traductor::t("modificarDiagnostico.tituloModulo") ?></h2>

        <form class="formulario" id="formModificarDiagnostico">

            <label for="modificarDiagnosticoSelect"><?= Traductor::t("modificarDiagnostico.labelSelect") ?></label>
            <select id="modificarDiagnosticoSelect" name="idDiagnostico" required>
                <option value=""><?= Traductor::t("modificarDiagnostico.opcionSeleccione") ?></option>
            </select>

            <label for="modificarDiagnosticoTexto"><?= Traductor::t("modificarDiagnostico.labelTexto") ?></label>
            <textarea id="modificarDiagnosticoTexto" name="diagnostico" rows="4" minlength="10" required></textarea>

            <button class="boton-principal" type="submit"><?= Traductor::t("modificarDiagnostico.btnGuardar") ?></button>
        </form>
    </section>

    <script src="<?= URL_BASE ?>/public/assets/js/modificarDiagnostico.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>