<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>Vista de Tickets</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/vistaTicket.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/vistaTicket2.css">
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
                <li><a class="cerrarSesion" href="cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>



    <section class="VistaDeTickets">

        <h1><?= Traductor::t("vistaTickets.titulo") ?></h1>

        <input type="text" id="buscadorDeTickets" placeholder="<?= Traductor::t("vistaTickets.placeholderBuscador") ?>">
        <button id="buscarTicket"><?= Traductor::t("vistaTickets.btnBuscar") ?></button>

        <p><?= Traductor::t("vistaTickets.subtitulo") ?></p>

        <select id="filtroDeEquipos">
            <option value=""><?= Traductor::t("vistaTickets.opcionTodosEquipos") ?></option>
        </select>

        <select id="filtroPrioridad">

            <option value=""><?= Traductor::t("vistaTickets.opcionTodasPrioridades") ?></option>

            <option value="Indefinida"><?= Traductor::t("vistaTickets.prioridadIndefinida") ?></option>

            <option value="Alta"><?= Traductor::t("vistaTickets.prioridadAlta") ?></option>

            <option value="Media"><?= Traductor::t("vistaTickets.prioridadMedia") ?></option>

            <option value="Baja"><?= Traductor::t("vistaTickets.prioridadBaja") ?></option>
        </select>

        <select id="filtroEstado">

            <option value=""><?= Traductor::t("vistaTickets.opcionTodosEstados") ?></option>
            <option value="Pendiente"><?= Traductor::t("vistaTickets.estadoPendiente") ?></option>
            <option value="En Proceso"><?= Traductor::t("vistaTickets.estadoEnProceso") ?></option>
            <option value="Resuelto"><?= Traductor::t("vistaTickets.estadoResuelto") ?></option>
            <option value="Cerrado"><?= Traductor::t("vistaTickets.estadoCerrado") ?></option>

        </select>

        <input type="date" id="fechaDesde">
        <input type="date" id="fechaHasta">
        <button id="filtrarFechas"><?= Traductor::t("vistaTickets.btnFiltrarFechas") ?></button>


    </section>

    <section id="listaTickets"></section>

    <script src="<?= URL_BASE ?>/public/assets/js/vistaTickets.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/actualizarTicket.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtros.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/buscadorDeTickets.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtroDeFechas.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>