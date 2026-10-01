<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docente</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
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
                <li><a href="<?= URL_BASE ?>/public/SolicitarLab.php"><?= Traductor::t("nav.solicitarLab") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/IngresoDeTickets.php"><?= Traductor::t("nav.ingresarTicket") ?></a></li>
                <li><a class="cerrarSesion" href="<?= URL_BASE ?>/public/cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("docente.bienvenida") ?> <?= htmlspecialchars($usuario->getNombre()) ?> <?= htmlspecialchars($usuario->getApellido()) ?> <?= Traductor::t("docente.rolSufijo") ?></h1>
    </section>
    <section class="modulo-imagen">
        <p><?= Traductor::t("docente.instruccion") ?></p>
        <img src="<?= URL_BASE ?>/public/assets/img/UTU_9296-1024x614.jpg" alt="UTU_9296-1024x614" width="500px">
    </section>
    </body>
    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    </html>