<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Traductor::t("registrarIntervencion.titulo") ?></title>
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
        <li><a href="HistorialTecnico.php"><?= Traductor::t("nav.historialTecnico") ?></a></li>
        <li><a href="cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
        <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
        <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
      </ul>
    </nav>
    <h1>S.G.R.S.I</h1>
    <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75">
  </header>

  <section class="encabezado">
    <h1><?= Traductor::t("registrarIntervencion.titulo") ?></h1>
    <p><?= Traductor::t("registrarIntervencion.subtitulo") ?></p>
  </section>

  <p id="mensajeIntervencion" role="status"></p>

  <section class="modulo" id="registrarIntervencion">
    <h2><?= Traductor::t("registrarIntervencion.tituloModulo") ?></h2>
    <form class="formulario" id="formRegistrarIntervencion">
      <label for="intervencionEquipoSelect"><?= Traductor::t("registrarIntervencion.labelEquipo") ?></label>
      <select id="intervencionEquipoSelect" name="idEquipo" required>
        <option value=""><?= Traductor::t("registrarIntervencion.opcionSeleccioneEquipo") ?></option>
      </select>

      <label for="intervencionTipo"><?= Traductor::t("registrarIntervencion.labelTipo") ?></label>
      <input id="intervencionTipo" name="tipo" type="text" maxlength="60" required>

      <label for="intervencionDescripcion"><?= Traductor::t("registrarIntervencion.labelDescripcion") ?></label>
      <textarea id="intervencionDescripcion" name="descripcion" rows="4" minlength="10" maxlength="2000" required></textarea>

      <button class="boton-principal" type="submit"><?= Traductor::t("registrarIntervencion.btnRegistrar") ?></button>
    </form>
  </section>

  <script src="<?= URL_BASE ?>/public/assets/js/RegistrarIntervencion.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>