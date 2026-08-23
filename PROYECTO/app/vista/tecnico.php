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
                <li><a href="<?= URL_BASE ?>/public/VistaLab.php">Solicitudes de laboratorio</a></li>
                <li><a href="<?= URL_BASE ?>/public/VistaDeTickets.php">Tickets</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarDiagnostico.php">Registrar diagnostico</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarIntervencion.html">Registrar intervencion</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarReemplazo.html">Registrar reemplazo</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarReparacion.html">Registrar reparacion</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarSolucion.php">Registrar solucion</a></li>
                <li><a href="<?= URL_BASE ?>/public/ConsultarDiagnostico.php">Consultar diagnosticos</a></li>
                <li><a href="<?= URL_BASE ?>/public/ConsultaEstados.html">Consultar Estados</a></li>
                <li><a href="<?= URL_BASE ?>/public/Equipos.php">Consultar equipos</a></li>
                <li><a href="<?= URL_BASE ?>/public/ConsultarMovimientos.html">Consultar movimientos</a></li>
                <li><a href="<?= URL_BASE ?>/public/CambioUbicacion.html">Registrar cambio de ubicacion de dispositivos</a></li>
                <li><a href="<?= URL_BASE ?>/public/HistorialTecnico.html">Historial Tecnico</a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1>Bienvenido, <?= htmlspecialchars($usuario->getNombre()) ?> <?= htmlspecialchars($usuario->getApellido()) ?> (Tecnico)</h1>
        <p>Haga uso del menu para comenzar.</p>
    </section>

    <main>
        <section class="Estadistica">
            <article class="CartaEstadistica">
                <p class="Puntito1"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantReportes"><?= htmlspecialchars($totalReportes) ?></p>
                <p class="EtiquetaEstadistica">Reportes</p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito2"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="canIncidenciasAbiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></p>
                <p class="EtiquetaEstadistica">Incidencias sin atender</p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito3"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantEnProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></p>
                <p class="EtiquetaEstadistica">En Proceso</p>
            </article>
            <article class="CartaEstadistica">
                <p class="Puntito4"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/circle-fill.svg" width="12" alt=""></p>
                <p class="CantidadEstadistica" id="cantResueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></p>
                <p class="EtiquetaEstadistica">Resueltas</p>
            </article>
        </section>

        <section class="PanelGrafico">
            <h2>Incidencias por estado</h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoA"></span> Pendiente</span></td>
                        <td class="contador" id="contador-Abiertas"><?= htmlspecialchars($porEstado["Pendiente"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoB"></span> En Proceso</span></td>
                        <td class="contador" id="contador-enProceso"><?= htmlspecialchars($porEstado["En Proceso"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoC"></span> Resuelto</span></td>
                        <td class="contador" id="contador-resueltas"><?= htmlspecialchars($porEstado["Resuelto"]) ?></td>
                    </tr>
                    <tr>
                        <td><span class="etiqueta-estado"><span class="puntoD"></span> Cerrado</span></td>
                        <td class="contador" id="contador-Cerradas"><?= htmlspecialchars($porEstado["Cerrado"]) ?></td>
                    </tr>
                </tbody>
                <tfoot class="total-estado">
                    <tr>
                        <td>Total</td>
                        <td id="cuentaTotal"><?= htmlspecialchars($totalReportes) ?></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="PanelGrafico">
            <h2>Tiempos de Resolución</h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Días</th>
                    </tr>
                </thead>
                <tbody id="tbodyTiempos">
                    <?php if (empty($tiemposResolucion)): ?>
                        <tr>
                            <td colspan="2">Todavía no hay tickets resueltos</td>
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
            <h2>Incidencias por salón</h2>
            <table class="tablaEstado">
                <thead>
                    <tr>
                        <th>Salón</th>
                        <th>Incidencias</th>
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