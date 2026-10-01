<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Diagnósticos</title>
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
                <li><a href="<?= URL_BASE ?>/public/Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarDiagnostico.php"><?= Traductor::t("nav.registrarDiagnostico") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarSolucion.php"><?= Traductor::t("nav.registrarSolucion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>


<header class="encabezado">
    <h1><?= Traductor::t("consultarDiagnostico.titulo") ?></h1>
    <p><?= Traductor::t("consultarDiagnostico.subtitulo") ?></p>
</header>

<section class="modulo" id="filtroDiagnosticos">
    <form class="formulario" id="formFiltroDiagnosticos">
        <label for="filtroTicket"><?= Traductor::t("consultarDiagnostico.labelFiltrarTicket") ?></label>
        <input type="text" id="filtroTicket" name="ticket" placeholder="Ej: INC-2026-0001">
        <button class="boton-principal" type="submit"><?= Traductor::t("consultarDiagnostico.btnFiltrar") ?></button>
        <button class="boton-principal" type="button" id="btnQuitarFiltroTicket" hidden><?= Traductor::t("consultarDiagnostico.btnQuitarFiltro") ?></button>
    </form>
</section>

<section class="modulo" id="consultarDiagnostico">
    <h2 id="tituloConsultarDiagnosticos"><?= Traductor::t("consultarDiagnostico.tituloTabla") ?></h2>

    <table id="tablaDiagnosticos" class="tabla">
        <thead hidden>
            <tr>
                <th class="tabla-th"><?= Traductor::t("consultarDiagnostico.thId") ?></th>
                <th class="tabla-th"><?= Traductor::t("consultarDiagnostico.thTicket") ?></th>
                <th class="tabla-th"><?= Traductor::t("consultarDiagnostico.thDiagnostico") ?></th>
                <th class="tabla-th"><?= Traductor::t("consultarDiagnostico.thFecha") ?></th>
                <th class="tabla-th"><?= Traductor::t("consultarDiagnostico.thTecnico") ?></th>
            </tr>
        </thead>
        <tbody id="cuerpoTablaDiagnosticos">
            <tr>
                <td colspan="5" class="tabla-vacia"><?= Traductor::t("consultarDiagnostico.cargando") ?></td>
            </tr>
        </tbody>
    </table>
</section>

<script src="<?= URL_BASE ?>/public/assets/js/consultarDiagnostico.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>