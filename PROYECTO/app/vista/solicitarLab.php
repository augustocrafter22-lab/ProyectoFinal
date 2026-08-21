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
                <li><a href="Docente.php">Regresar</a></li>
                <li><a class="cerrarSesion" href="Login.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <section class="encabezado">
        <h1>Solicitud de Laboratorio</h1>
        <p>¡Hola! Esta seccion es para solicitar la preparación de un laboratorio</p>
    </section>

    <section class="modulo" id="ingresoLaboratorio">

        <form class="formulario" id="LabForm" method="POST" action="procesarSolicitudLaboratorio.php">

            <label for="laboratorioSolicitud">Laboratorios</label>
            <select name="idLaboratorio" id="laboratorioSolicitud" required>
                <option value="">Seleccione un espacio</option>
                <?php foreach ($laboratorios as $lab): ?>
                    <option value="<?= htmlspecialchars($lab["idLaboratorio"]) ?>">
                        <?= htmlspecialchars($lab["numeroLaboratorio"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="SolicitudDeSoftware">Solicitud de software (En caso de ser necesaria, por favor aclarar el
                nombre
                y version del software solicitado en el Detalle)</label>
            <select name="solicitaSoftware" id="SolicitudDeSoftware">
                <option value="No">No</option>
                <option value="Si">Si</option>
            </select>

            <label for="DetalleSoftware">Detalle (Escribir cualquier requerimiento adicional en el detalle)</label>
            <textarea name="detalle" id="DetalleSoftware" rows="4" minlength="2"></textarea>

            <label for="Restricciones">Restricciones para los alumnos</label>
            <textarea name="restricciones" id="Restricciones"></textarea>

            <label for="FechaEstimada">Fecha Estimada</label>
            <input type="date" name="fechaEstimada" id="FechaEstimada" required>

            <label for="HoraEstimada">Hora estimada</label>
            <input type="time" name="horaEstimada" id="HoraEstimada" required>

            <button class="boton-principal" type="submit">Publicar Solicitud</button>

        </form>


</body>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/Laboratorio.js"></script>

</html>