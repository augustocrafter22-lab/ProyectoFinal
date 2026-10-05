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
                <li><a href="<?= URL_BASE ?>/public/paginas/Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("vistaLab.titulo") ?></h1>
        <p><?= Traductor::t("vistaLab.subtitulo") ?></p>
    </section>

    <section class="modulo" id="VisualizacionLaboratorio">
        <fieldset class="controles">
            <legend><?= Traductor::t("vistaLab.legendFiltros") ?></legend>

            <label for="filtroPorFecha"><?= Traductor::t("vistaLab.labelFecha") ?></label>
            <input type="date" id="filtroPorFecha">

            <label for="filtroPorLaboratorio"><?= Traductor::t("vistaLab.labelLaboratorio") ?></label>
            <select id="filtroPorLaboratorio">
                <option value=""><?= Traductor::t("vistaLab.opcionTodos") ?></option>
                <?php foreach ($laboratorios as $lab): ?>
                    <option value="<?= htmlspecialchars($lab["numeroLaboratorio"]) ?>">
                        <?= htmlspecialchars($lab["numeroLaboratorio"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button id="btnLimpiarFiltro" type="button"><?= Traductor::t("vistaLab.btnLimpiarFiltros") ?></button>
        </fieldset>

        <table id="tablaLaboratorio">
            <thead>
                <tr>
                    <th><?= Traductor::t("vistaLab.thId") ?></th>
                    <th><?= Traductor::t("vistaLab.thLaboratorio") ?></th>
                    <th><?= Traductor::t("vistaLab.thSoftware") ?></th>
                    <th><?= Traductor::t("vistaLab.thDetalle") ?></th>
                    <th><?= Traductor::t("vistaLab.thRestricciones") ?></th>
                    <th><?= Traductor::t("vistaLab.thFecha") ?></th>
                    <th><?= Traductor::t("vistaLab.thHora") ?></th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s["idSolicitud"]) ?></td>
                        <td><?= htmlspecialchars($s["numeroLaboratorio"]) ?></td>
                        <td><?= $s["solicitaSoftware"] ? Traductor::t("vistaLab.si") : Traductor::t("vistaLab.no") ?></td>
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