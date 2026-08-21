<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista-Laboratorio</title>
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
                <li><a href="<?= URL_BASE ?>/public/Tecnico.php">Regresar</a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1>Vista de Laboratorio</h1>
        <p>¡Hola! Esta seccion es para visualizar la solicitudes de preparacion de laboratorios</p>
    </section>

    <section class="modulo" id="VisualizacionLaboratorio">
        <fieldset class="controles">
            <legend>Filtros</legend>

            <label for="filtroPorFecha">Fecha:</label>
            <input type="date" id="filtroPorFecha">

            <label for="filtroPorLaboratorio">Laboratorio:</label>
            <select id="filtroPorLaboratorio">
                <option value="">Todos</option>
                <?php foreach ($laboratorios as $lab): ?>
                    <option value="<?= htmlspecialchars($lab["numeroLaboratorio"]) ?>">
                        <?= htmlspecialchars($lab["numeroLaboratorio"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button id="btnLimpiarFiltro" type="button">Limpiar filtros</button>
        </fieldset>

        <table id="tablaLaboratorio">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Laboratorio</th>
                    <th>Software</th>
                    <th>Detalle</th>
                    <th>Restricciones</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s["idSolicitud"]) ?></td>
                        <td><?= htmlspecialchars($s["numeroLaboratorio"]) ?></td>
                        <td><?= $s["solicitaSoftware"] ? "Sí" : "No" ?></td>
                        <td><?= htmlspecialchars($s["detalle"] ?? "") ?></td>
                        <td><?= htmlspecialchars($s["restricciones"] ?? "") ?></td>
                        <td><?= htmlspecialchars($s["fechaEstimada"]) ?></td>
                        <td><?= htmlspecialchars($s["horaEstimada"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/vistaLab.js"></script>
</body>

</html>