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
            <option value="PC-01">PC-01</option>
            <option value="PC-02">PC-02</option>
            <option value="PC-03">PC-03</option>
            <option value="PC-04">PC-04</option>
            <option value="PC-05">PC-05</option>
            <option value="PC-06">PC-06</option>
            <option value="PC-07">PC-07</option>
            <option value="PC-08">PC-08</option>
            <option value="PC-09">PC-09</option>
            <option value="PC-10">PC-10</option>
            <option value="PC-11">PC-11</option>
            <option value="PC-12">PC-12</option>
            <option value="PC-13">PC-13</option>
            <option value="PC-14">PC-14</option>
            <option value="PC-15">PC-15</option>
            <option value="PC-16">PC-16</option>

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

    <section id="listaTickets">

        <?php foreach ($tickets as $ticket) { ?>

            <article class="ticket" data-fecha="<?= htmlspecialchars($ticket["fechaCreacion"]) ?>">

                <section class="ticketInfo">
                    <h3>
                        <a href="ConsultarDiagnostico.php?ticket=<?= htmlspecialchars($ticket["idTicket"]) ?>" class="ticket-enlace">
                            <?= htmlspecialchars($ticket["idTicket"]) ?>
                        </a>
                    </h3>

                    <p><?= htmlspecialchars($ticket["asunto"]) ?></p>

                    <p><?= htmlspecialchars($ticket["equipo"]) ?></p>
                </section>

                <section class="ticketEstado">

                    <select class="select-estado">
                        <?php foreach (["Pendiente", "En Proceso", "Resuelto", "Cerrado"] as $opcion) { ?>
                            <option value="<?= htmlspecialchars($opcion) ?>" <?= $ticket["estado"] === $opcion ? "selected" : "" ?>>
                                <?= htmlspecialchars($opcion) ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select class="select-prioridad">
                        <?php foreach (["Indefinida", "Alta", "Media", "Baja"] as $opcion) { ?>
                            <option value="<?= htmlspecialchars($opcion) ?>" <?= $ticket["prioridad"] === $opcion ? "selected" : "" ?>>
                                Prioridad: <?= htmlspecialchars($opcion) ?>
                            </option>
                        <?php } ?>
                    </select>

                    <p class="laboratorio"><?= htmlspecialchars($ticket["laboratorio"]) ?></p>

                    <button type="button" class="btn-finalizar">Finalizar Ticket</button>

                    <?php if (!empty($ticket["fechaFinalizacion"])) { ?>
                        <p>Finalizado: <?= htmlspecialchars($ticket["fechaFinalizacion"]) ?></p>
                    <?php } ?>

                </section>

            </article>

        <?php } ?>

    </section>

    <script src="<?= URL_BASE ?>/public/assets/js/IngresoTickets.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtros.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/buscadorDeTickets.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtroDeFechas.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>