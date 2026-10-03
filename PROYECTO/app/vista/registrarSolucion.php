<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Solucion</title>
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
                <li><a href="Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="ConsultarDiagnostico.php"><?= Traductor::t("nav.consultarDiagnosticos") ?></a></li>
                <li><a href="RegistrarReparacion.php"><?= Traductor::t("nav.registrarReparacion") ?></a></li>
                <li><a href="cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>
<header class="encabezado">

<h1><?= Traductor::t("registrarSolucion.titulo") ?></h1>
<p><?= Traductor::t("registrarSolucion.subtitulo") ?></p>
</header>

    <p id="mensajeRegistrarSolucion" role="status"></p>

<section class="modulo" id="Solucion">
  <h2><?= Traductor::t("registrarSolucion.tituloModulo") ?></h2>

  <p id="avisoSinDiagnosticos" hidden><?= Traductor::t("registrarSolucion.avisoSinDiagnosticos") ?></p>

  <form class="formulario" id="formRegistrarSolucion">
    <label for="registrarSolucionDiagnostico"><?= Traductor::t("registrarSolucion.labelDiagnostico") ?></label>
    <select id="registrarSolucionDiagnostico" name="idDiagnostico" required>
      <option value=""><?= Traductor::t("registrarSolucion.opcionSeleccioneDiagnostico") ?></option>
    </select>

    <label for="registrarSolucionSolucion"><?= Traductor::t("registrarSolucion.labelSolucion") ?></label>
    <textarea id="registrarSolucionSolucion" name="solucion" rows="4" minlength="10" required></textarea>

    <button class="boton-principal" type="submit"><?= Traductor::t("registrarSolucion.btnRegistrar") ?></button>
  </form>
</section>
<script>
    window.cedulaTecnico = <?= json_encode($_SESSION["cedula"]) ?>;
</script>
<script src="<?= URL_BASE ?>/public/assets/js/registrarSolucion.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>