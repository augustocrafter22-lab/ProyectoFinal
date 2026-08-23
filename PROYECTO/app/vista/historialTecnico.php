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
        <li><a href="cerrarSesion.php">Cerrar sesion</a></li>
      </ul>
    </nav>
    <h1>S.G.R.S.I</h1>
  </header>

  <header class="encabezado">
    <h1>Historial Técnico</h1>
    <p>Consulte las reparaciones, intervenciones y reemplazos de un equipo.</p>
  </header>

  <section class="modulo" id="historialTecnico" style="max-width: 780px;">
    <h2>Historial técnico del equipo</h2>
    <label for="historialTecnicoEquipoSelect">Equipo</label>
    <select id="historialTecnicoEquipoSelect" style="width:100%; padding:10px 14px; margin-bottom:18px;">
      <option value="">Seleccione un equipo</option>
      <?php for ($numeroEquipo = 1; $numeroEquipo <= 10; $numeroEquipo++) {
          $equipo = sprintf("PC-%02d", $numeroEquipo);
      ?>
        <option value="<?= $equipo ?>"><?= $equipo ?></option>
      <?php } ?>
    </select>

    <table id="tablaHistorialTecnico" style="width:100%; border-collapse:collapse; font-size:14px;"></table>
  </section>

  <script>
    window.reparacionesPersistidas = <?= json_encode(array_map(function ($reparacion) {
        return [
            "equipoId" => $reparacion["equipo"],
            "descripcion" => $reparacion["solucion"],
            "fecha" => $reparacion["fechaSolucion"],
            "tecnico" => $reparacion["cedulaTecnico"]
        ];
    }, $reparaciones), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>
  <script src="<?= URL_BASE ?>/public/assets/js/HistorialTecnico.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>
