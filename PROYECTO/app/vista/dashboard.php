<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                <li><a href="<?= URL_BASE ?>/public/paginas/<?= $paginaInicio ?>"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("dashboard.titulo") ?></h1>
        <p><?= Traductor::t("dashboard.subtitulo") ?></p>
    </section>

    <main>
        <section class="Estadistica">
            <article class="CartaEstadistica">
                <p class="Puntito1"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantReportes"><?= htmlspecialchars($totalReportes) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("dashboard.reportes") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito2"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="canIncidenciasAbiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("dashboard.incidenciasSinAtender") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito3"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantEnProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("dashboard.enProceso") ?></p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito4"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantResueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></p>
                <p class="EtiquetaEstadistica"><?= Traductor::t("dashboard.resueltas") ?></p>
            </article>
        </section>

        <section class="PanelGrafico">
            <h2><?= Traductor::t("dashboard.panelIncidenciasPorEstado") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("dashboard.thEstado") ?></th>
                        <th><?= Traductor::t("dashboard.thCantidad") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoA"></span> <?= Traductor::t("dashboard.estadoPendiente") ?></span></td>
                        <td class="contador" id="contador-Abiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoB"></span> <?= Traductor::t("dashboard.estadoEnProceso") ?></span></td>
                        <td class="contador" id="contador-enProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoC"></span> <?= Traductor::t("dashboard.estadoResuelto") ?></span></td>
                        <td class="contador" id="contador-resueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoD"></span> <?= Traductor::t("dashboard.estadoCerrado") ?></span></td>
                        <td class="contador" id="contador-Cerradas"><?= htmlspecialchars($porEstado["Cerrado"]) ?></td>
                    </tr>
                </tbody>
                <tfoot class="total-estado">
                    <tr>
                        <td><?= Traductor::t("dashboard.total") ?></td>
                        <td id="cuentaTotal"><?= htmlspecialchars($totalReportes) ?></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="PanelGrafico">
            <h2><?= Traductor::t("dashboard.panelTiempos") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("dashboard.thTicket") ?></th>
                        <th><?= Traductor::t("dashboard.thDias") ?></th>
                    </tr>
                </thead>
                <tbody id="tbodyTiempos">
                    <?php if (empty($tiemposResolucion)): ?>
                        <tr>
                            <td colspan="2"><?= Traductor::t("dashboard.sinTicketsResueltos") ?></td>
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
            <h2><?= Traductor::t("dashboard.panelIncidenciasPorSalon") ?></h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th><?= Traductor::t("dashboard.thSalon") ?></th>
                        <th><?= Traductor::t("dashboard.thIncidencias") ?></th>
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