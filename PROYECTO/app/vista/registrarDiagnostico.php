<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Diagnostico</title>
  <link rel="stylesheet" href="../assets/Css/style.css">
  <link rel="stylesheet" href="../Assets/Css/barraNavegación.css">
</head>
<body>

  <header class="BarraNavegacion">

        <nav>
            <button class="btnMenu" id="btnMenu" type="button"><img class="menu"
                    src="../Assets/Imagenes/Bootstrap/list.svg" alt="menu" width="40" height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="../Assets/Imagenes/Bootstrap/x.svg" alt="X" class="menu" width="40" height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="Tecnico.html">Regresar</a></li>
                <li><a href="ModificarDiagnostico.php">Modificar Diagnostico</a></li>
                <li><a href="ConsultarDiagnostico.php">Consultar diagnosticos</a></li>
                <li><a href="RegistrarIntervencion.html">Registrar intervencion</a></li>
                <li><a href="RegistrarReemplazo.html">Registrar reemplazo</a></li>
                <li><a href="RegistrarReparacion.html">Registrar reparacion</a></li>
                <li><a href="RegistrarSolucion.php">Registrar solucion</a></li>
                <li><a href="cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="../Assets/Imagenes/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

<h1>Registro de Diagnostico</h1>
    <p>Aqui se podran registrar los diagnósticos técnicos en base al ticket.</p>

    <?php if (isset($_GET["error"])) { ?>
        <p class="mensaje-error"><?= htmlspecialchars($_GET["error"]) ?></p>
    <?php } ?>

    <?php if (isset($_GET["exito"])) { ?>
        <p class="mensaje-exito"><?= htmlspecialchars($_GET["exito"]) ?></p>
    <?php } ?>

<section class="modulo" id="registrarDiagnostico">
  <h2>Registrar diagnósticos</h2>

  <form class="formulario" id="formregistrarDiagnostico" action="<?= URL_BASE ?>/app/controlador/procesarRegistrarDiagnostico.php" method="POST">
    <label for="registrarDiagnosticoTicket">Ticket</label>
    <select id="registrarDiagnosticoTicket" name="idTicket" required>
        <option value="">Seleccione un ticket</option>
        <?php foreach ($tickets as $ticket) { ?>
            <option value="<?= htmlspecialchars($ticket["idTicket"]) ?>">
                <?= htmlspecialchars($ticket["idTicket"]) ?> - <?= htmlspecialchars($ticket["asunto"]) ?>
            </option>
        <?php } ?>
    </select>

    <label for="registrarDiagnosticoDiagnostico">Diagnóstico técnico</label>
    <textarea id="registrarDiagnosticoDiagnostico" name="diagnostico" rows="4" minlength="10" required></textarea>

    <button class="boton-principal" type="submit">Registrar diagnóstico</button>
  </form>
</section>
    <script src="../Assets/JS/barraNavegacion.js"></script>
    </body>
</html>