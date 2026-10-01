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
                <li><a href="Tecnico.php">Regresar</a></li>
                <li><a class="cerrarSesion" href="cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>



    <section class="VistaDeTickets">

        <h1>Vista de Tickets</h1>

        <input type="text" id="buscadorDeTickets" placeholder="Buscar por número de ticket...">
        <button id="buscarTicket">Buscar</button>

        <p>Aquí se podrán visualizar los tickets ingresados, su estado actual y prioridad. Además, se podrán actualizar los estados de los tickets a medida que se vayan resolviendo las incidencias.</p>

        <select id="filtroDeEquipos">
            <option value="">Todos los equipos</option>
        </select>

        <select id="filtroPrioridad">

            <option value="">Todas las prioridades</option>

            <option value="Indefinida">Indefinida</option>

            <option value="Alta">Alta</option>

            <option value="Media">Media</option>

            <option value="Baja">Baja</option>
        </select>

        <select id="filtroEstado">

            <option value="">Todos los estados</option>
            <option value="Pendiente">Pendiente</option>
            <option value="En Proceso">En Proceso</option>
            <option value="Resuelto">Resuelto</option>
            <option value="Cerrado">Cerrado</option>

        </select>

        <input type="date" id="fechaDesde">
        <input type="date" id="fechaHasta">
        <button id="filtrarFechas">Filtrar fechas</button>


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