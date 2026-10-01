<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnico</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/Dashboard.css">
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
                <li><a href="<?= URL_BASE ?>/public/VistaLab.php"><?= Traductor::t("nav.solicitudesLab") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/VistaDeTickets.php"><?= Traductor::t("nav.tickets") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarDiagnostico.php"><?= Traductor::t("nav.registrarDiagnostico") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarReparacion.php"><?= Traductor::t("nav.registrarReparacion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarSolucion.php"><?= Traductor::t("nav.registrarSolucion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/ConsultarDiagnostico.php"><?= Traductor::t("nav.consultarDiagnosticos") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/Equipos.php"><?= Traductor::t("nav.consultarEquipos") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/HistorialTecnico.php"><?= Traductor::t("nav.historialTecnico") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("tecnico.bienvenida") ?> <?= htmlspecialchars($usuario->getNombre()) ?> <?= htmlspecialchars($usuario->getApellido()) ?> <?= Traductor::t("tecnico.rolSufijo") ?></h1>
        <p><?= Traductor::t("tecnico.instruccion") ?></p>
    </section>

    <main>
        <section class="Estadistica">
            <article class="CartaEstadistica">
                <p class="Puntito1"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantReportes"><?= htmlspecialchars($totalReportes) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("tecnico.reportes") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito2"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="canIncidenciasAbiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("tecnico.incidenciasSinAtender") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito3"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantEnProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("tecnico.enProceso") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito4"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantResueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("tecnico.resueltas") ?></p>
            </article>
        </section>

        <section class="PanelGrafico">
            <h2><?= Traductor::t("tecnico.panelIncidenciasPorEstado") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("tecnico.thEstado") ?></th>
                        <th><?= Traductor::t("tecnico.thCantidad") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoA"></span> <?= Traductor::t("tecnico.estadoPendiente") ?></span></td>
                        <td class="contador" id="contador-Abiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoB"></span> <?= Traductor::t("tecnico.estadoEnProceso") ?></span></td>
                        <td class="contador" id="contador-enProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoC"></span> <?= Traductor::t("tecnico.estadoResuelto") ?></span></td>
                        <td class="contador" id="contador-resueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoD"></span> <?= Traductor::t("tecnico.estadoCerrado") ?></span></td>
                        <td class="contador" id="contador-Cerradas"><?= htmlspecialchars($porEstado["Cerrado"]) ?></td>
                    </tr>
                </tbody>
                <tfoot class="total-estado">
                    <tr>
                        <td><?= Traductor::t("tecnico.total") ?></td>
                        <td id="cuentaTotal"><?= htmlspecialchars($totalReportes) ?></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="PanelGrafico">
            <h2><?= Traductor::t("tecnico.panelTiempos") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("tecnico.thTicket") ?></th>
                        <th><?= Traductor::t("tecnico.thDias") ?></th>
                    </tr>
                </thead>
                <tbody id="tbodyTiempos">
                    <?php if (empty($tiemposResolucion)): ?>
                        <tr>
                            <td colspan="2"><?= Traductor::t("tecnico.sinTicketsResueltos") ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tiemposResolucion as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t["idTicket"]) ?></td>
                                <td><?= htmlspecialchars($t["dias"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="PanelGrafico">
            <h2><?= Traductor::t("tecnico.panelIncidenciasPorSalon") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("tecnico.thSalon") ?></th>
                        <th><?= Traductor::t("tecnico.thIncidencias") ?></th>
                    </tr>
                </thead>
                <tbody id="tbodySalones">
                    <?php foreach ($incidenciasPorSalon as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s["laboratorio"]) ?></td>
                            <td><?= htmlspecialchars($s["cantidad"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>