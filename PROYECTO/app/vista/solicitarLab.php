<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes-Laboratorio</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
</head>

<body>
    <header class="BarraNavegacion">

        <nav>
            <button class="btnMenu" id="btnMenu" type="button"><img class="menu"
                    src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40"
                    height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40"
                    height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="Docente.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a class="cerrarSesion" href="cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1><?= Traductor::t("solicitarLab.titulo") ?></h1>
        <p><?= Traductor::t("solicitarLab.subtitulo") ?></p>
    </section>

    <section class="modulo" id="ingresoLaboratorio">

        <form class="formulario" id="LabForm" method="POST" action="procesarSolicitudLaboratorio.php">

            <label for="laboratorioSolicitud"><?= Traductor::t("solicitarLab.labelLaboratorios") ?></label>
            <select name="idLaboratorio" id="laboratorioSolicitud" required>
                <option value=""><?= Traductor::t("solicitarLab.opcionSeleccioneEspacio") ?></option>
                <?php foreach ($laboratorios as $lab): ?>
                    <option value="<?= htmlspecialchars($lab["idLaboratorio"]) ?>">
                        <?= htmlspecialchars($lab["numeroLaboratorio"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="SolicitudDeSoftware"><?= Traductor::t("solicitarLab.labelSolicitudSoftware") ?></label>
            <select name="solicitaSoftware" id="SolicitudDeSoftware">
                <option value="No"><?= Traductor::t("solicitarLab.opcionNo") ?></option>
                <option value="Si"><?= Traductor::t("solicitarLab.opcionSi") ?></option>
            </select>

            <label for="DetalleSoftware"><?= Traductor::t("solicitarLab.labelDetalle") ?></label>
            <textarea name="detalle" id="DetalleSoftware" rows="4" minlength="2"></textarea>

            <label for="Restricciones"><?= Traductor::t("solicitarLab.labelRestricciones") ?></label>
            <textarea name="restricciones" id="Restricciones"></textarea>

            <label for="FechaEstimada"><?= Traductor::t("solicitarLab.labelFechaEstimada") ?></label>
            <input type="date" name="fechaEstimada" id="FechaEstimada" required>

            <label for="HoraEstimada"><?= Traductor::t("solicitarLab.labelHoraEstimada") ?></label>
            <input type="time" name="horaEstimada" id="HoraEstimada" required>

            <button class="boton-principal" type="submit"><?= Traductor::t("solicitarLab.btnPublicar") ?></button>

        </form>


</body>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/Laboratorio.js"></script>

</html>