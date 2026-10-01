<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Diagnostico</title>
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
                <li><a href="ModificarDiagnostico.php"><?= Traductor::t("nav.modificarDiagnostico") ?></a></li>
                <li><a href="ConsultarDiagnostico.php"><?= Traductor::t("nav.consultarDiagnosticos") ?></a></li>
                <li><a class="cerrarSesion" href="cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>
<section class="encabezado">
<h1><?= Traductor::t("registrarDiagnostico.titulo") ?></h1>
    <p><?= Traductor::t("registrarDiagnostico.subtitulo") ?></p>
</section>
    <p id="mensajeRegistrarDiagnostico" role="status"></p>

<section class="modulo" id="registrarDiagnostico">
  <h2><?= Traductor::t("registrarDiagnostico.tituloModulo") ?></h2>

  <form class="formulario" id="formregistrarDiagnostico">
    <label for="registrarDiagnosticoTicket"><?= Traductor::t("registrarDiagnostico.labelTicket") ?></label>
    <select id="registrarDiagnosticoTicket" name="idTicket" class="eligeTicket" required>
        <option value=""><?= Traductor::t("registrarDiagnostico.opcionSeleccioneTicket") ?></option>
    </select>

    <label for="registrarDiagnosticoDiagnostico"><?= Traductor::t("registrarDiagnostico.labelDiagnostico") ?></label>
    <textarea id="registrarDiagnosticoDiagnostico" name="diagnostico" rows="4" minlength="10" required></textarea>

    <button class="boton-principal" type="submit"><?= Traductor::t("registrarDiagnostico.btnRegistrar") ?></button>
  </form>
</section>
    <script>
        window.cedulaTecnico = <?= json_encode($_SESSION["cedula"]) ?>;
    </script>
    <script src="<?= URL_BASE ?>/public/assets/js/registrarDiagnostico.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    </body>
</html>