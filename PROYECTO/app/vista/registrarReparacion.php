<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
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
    <h1><?= Traductor::t("registrarReparacion.titulo") ?></h1>
    <p><?= Traductor::t("registrarReparacion.subtitulo") ?></p>
  </section>

  <p id="mensajeRegistrarReparacion" role="status"></p>

  <section class="modulo" id="registrarReparacion">
    <h2><?= Traductor::t("registrarReparacion.tituloModulo") ?></h2>

    <p id="avisoSinDiagnosticosReparacion" hidden><?= Traductor::t("registrarReparacion.avisoSinDiagnosticos") ?></p>

    <form class="formulario" id="formRegistrarReparacion">
      <label for="registrarReparacionDiagnostico"><?= Traductor::t("registrarReparacion.labelDiagnostico") ?></label>
      <select id="registrarReparacionDiagnostico" name="idDiagnostico" required>
        <option value=""><?= Traductor::t("registrarReparacion.opcionSeleccioneDiagnostico") ?></option>
      </select>
      <label for="registrarReparacionTexto"><?= Traductor::t("registrarReparacion.labelTexto") ?></label>
      <textarea id="registrarReparacionTexto" name="reparacion" rows="4" minlength="10" required></textarea>
      <button class="boton-principal" type="submit"><?= Traductor::t("registrarReparacion.btnRegistrar") ?></button>
    </form>
  </section>

  <script>
    window.cedulaTecnico = <?= json_encode($_SESSION["cedula"]) ?>;
  </script>
  <script src="<?= URL_BASE ?>/public/assets/js/RegistrarReparacion.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>