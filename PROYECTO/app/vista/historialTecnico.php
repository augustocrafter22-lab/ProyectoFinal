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
        <li><a href="Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
        <li><a href="cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
        <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
        <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
      </ul>
    </nav>
    <h1>S.G.R.S.I</h1>
    <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75">
  </header>

  <section class="encabezado">
    <h1><?= Traductor::t("historialTecnico.titulo") ?></h1>
    <p><?= Traductor::t("historialTecnico.subtitulo") ?></p>
  </section>

  <section class="modulo" id="historialTecnico" style="max-width: 780px;">
    <h2><?= Traductor::t("historialTecnico.tituloModulo") ?></h2>
    <label for="historialTecnicoEquipoSelect"><?= Traductor::t("historialTecnico.labelEquipo") ?></label>
    <select id="historialTecnicoEquipoSelect" required>
      <option value=""><?= Traductor::t("historialTecnico.opcionSeleccioneEquipo") ?></option>
    </select>

    <table id="tablaHistorialTecnico" style="width:100%; border-collapse:collapse; font-size:14px;"></table>
  </section>
</body>
  <script src="<?= URL_BASE ?>/public/assets/js/HistorialTecnico.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</html>
