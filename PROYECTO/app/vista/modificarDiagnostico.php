<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Diagnóstico</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
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
                <li><a href="ConsultarDiagnostico.php">Ver Diagnostico</a></li>
                <li><a class="cerrarSesion" href="cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

    <header class="encabezado">
        <h1>Modificar Diagnóstico</h1>
        <p>Seleccioná un diagnóstico registrado para modificar su contenido.</p>
    </header>

    <?php if (isset($_GET["error"])) { ?>
        <p class="mensaje-error"><?= htmlspecialchars($_GET["error"]) ?></p>
    <?php } ?>

    <?php if (isset($_GET["exito"])) { ?>
        <p class="mensaje-exito"><?= htmlspecialchars($_GET["exito"]) ?></p>
    <?php } ?>

    <section class="modulo" id="modificarDiagnostico">
        <h2>Editar diagnóstico</h2>

        <form class="formulario" id="formModificarDiagnostico" action="<?= URL_BASE ?>/app/controlador/procesarModificarDiagnostico.php" method="POST">

            <label for="modificarDiagnosticoSelect">Diagnóstico a modificar</label>
            <select id="modificarDiagnosticoSelect" name="idDiagnostico" required
                onchange="this.form.diagnostico.value = this.options[this.selectedIndex].dataset.texto || '';">
                <option value="">Seleccione un diagnóstico</option>
                <?php foreach ($diagnosticos as $diagnostico) { ?>
                    <option value="<?= htmlspecialchars($diagnostico["idDiagnostico"]) ?>"
                        data-texto="<?= htmlspecialchars($diagnostico["diagnostico"]) ?>">
                        <?= htmlspecialchars($diagnostico["idDiagnostico"]) ?> |
                        <?= htmlspecialchars($diagnostico["idTicket"]) ?> |
                        <?= htmlspecialchars($diagnostico["fechaDiagnostico"]) ?>
                    </option>
                <?php } ?>
            </select>

            <label for="modificarDiagnosticoTexto">Diagnóstico técnico</label>
            <textarea id="modificarDiagnosticoTexto" name="diagnostico" rows="4" minlength="10" required></textarea>

            <button class="boton-principal" type="submit">Guardar cambios</button>
        </form>
    </section>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>