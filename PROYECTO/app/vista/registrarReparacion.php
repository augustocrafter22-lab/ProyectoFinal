<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Reparación</title>
  <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
  <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
</head>
<body>
  <header class="BarraNavegacion">
    <nav>
      <button class="btnMenu" id="btnMenu" type="button"><img class="menu" src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40" height="40"></button>
      <button class="btnMenuC" id="btnMenuC" type="button"><img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40" height="40"></button>
      <ul class="listaNavegacion">
        <li><a href="Tecnico.php">Regresar</a></li>
        <li><a href="cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
      </ul>
    </nav>
    <h1>S.G.R.S.I</h1>
    <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75">
  </header>

  <section class="encabezado">
    <h1>Registrar Reparación</h1>
    <p>Registrá una reparación realizada sobre un equipo.</p>
  </section>

  <p id="mensajeRegistrarReparacion" role="status"></p>

  <section class="modulo" id="registrarReparacion">
    <h2>Nueva reparación</h2>

    <p id="avisoSinDiagnosticosReparacion" hidden>No hay diagnósticos registrados para asociar una reparación.</p>

    <form class="formulario" id="formRegistrarReparacion">
      <label for="registrarReparacionDiagnostico">Diagnóstico</label>
      <select id="registrarReparacionDiagnostico" name="idDiagnostico" required>
        <option value="">Seleccione un diagnóstico</option>
      </select>
      <label for="registrarReparacionTexto">Descripción de la reparación</label>
      <textarea id="registrarReparacionTexto" name="reparacion" rows="4" minlength="10" required></textarea>
      <button class="boton-principal" type="submit">Registrar reparación</button>
    </form>
  </section>

  <script>
    window.cedulaTecnico = <?= json_encode($_SESSION["cedula"]) ?>;
  </script>
  <script src="<?= URL_BASE ?>/public/assets/js/RegistrarReparacion.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>