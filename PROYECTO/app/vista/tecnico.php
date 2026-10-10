<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnico</title>
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
                <li><a href="<?= URL_BASE ?>/public/paginas/Dashboard.php"><?= Traductor::t("nav.dashboard") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/VistaLab.php"><?= Traductor::t("nav.solicitudesLab") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/VistaDeTickets.php"><?= Traductor::t("nav.tickets") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/RegistrarDiagnostico.php"><?= Traductor::t("nav.registrarDiagnostico") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/RegistrarReparacion.php"><?= Traductor::t("nav.registrarReparacion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/RegistrarIntervencion.php"><?= Traductor::t("nav.registrarIntervencion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/RegistrarReemplazo.php"><?= Traductor::t("nav.registrarReemplazo") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/RegistrarSolucion.php"><?= Traductor::t("nav.registrarSolucion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/ConsultarDiagnostico.php"><?= Traductor::t("nav.consultarDiagnosticos") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/Equipos.php"><?= Traductor::t("nav.consultarEquipos") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/Prestamos.php"><?= Traductor::t("nav.prestamos") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/HistorialTecnico.php"><?= Traductor::t("nav.historialTecnico") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("tecnico.bienvenida") ?> <?= htmlspecialchars($usuario->getNombre()) ?> <?= htmlspecialchars($usuario->getApellido()) ?> <?= Traductor::t("tecnico.rolSufijo") ?></h1>
        <p><?= Traductor::t("tecnico.instruccion") ?></p>
    </section>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>