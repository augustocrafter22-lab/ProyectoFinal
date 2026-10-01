<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial Técnico</title>
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
    <h1>Historial Técnico</h1>
    <p>Consultá las reparaciones registradas para un equipo.</p>
  </section>

  <section class="modulo" id="historialTecnico" style="max-width: 780px;">
    <h2>Reparaciones del equipo</h2>
    <label for="historialTecnicoEquipoSelect">Equipo</label>
    <select id="historialTecnicoEquipoSelect" required>
      <option value="">Seleccione un equipo</option>
    </select>

    <table id="tablaHistorialTecnico" style="width:100%; border-collapse:collapse; font-size:14px;"></table>
  </section>
</body>
  <script src="<?= URL_BASE ?>/public/assets/js/HistorialTecnico.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</html>
