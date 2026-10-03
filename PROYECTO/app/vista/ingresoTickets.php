<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingreso de Tickets</title>
  <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css" />
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
                <li><a href="<?= URL_BASE ?>/public/Docente.php"><?= Traductor::t("common.regresar") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php"><?= Traductor::t("common.cerrarSesion") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
                <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>

  <section class="encabezado">
    <h1><?= Traductor::t("ingresoTickets.titulo") ?></h1>
    <p><?= Traductor::t("ingresoTickets.subtitulo") ?></p>
  </section>

  <p id="mensajeIngresoTickets" role="status"></p>

  <section class="modulo" id="ingresoTickets">
    <form class="formulario" id="ticketForm">

      <label for="laboratorioTaller"><?= Traductor::t("ingresoTickets.labelEspacio") ?></label>
      <select name="laboratorio" id="laboratorioTaller" required>
        <option value=""><?= Traductor::t("ingresoTickets.opcionSeleccioneEspacio") ?></option>
        <?php foreach ($laboratorios as $laboratorio): ?>
          <option value="<?= htmlspecialchars($laboratorio["idLaboratorio"]) ?>">
            <?= htmlspecialchars($laboratorio["numeroLaboratorio"]) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="equipo"><?= Traductor::t("ingresoTickets.labelEquipos") ?></label>
      <select name="equipo" id="equipo" required>
        <option value=""><?= Traductor::t("ingresoTickets.opcionSeleccioneEquipo") ?></option>
      </select>

      <label for="asunto"><?= Traductor::t("ingresoTickets.labelAsunto") ?></label>
      <input type="text" id="asunto" name="asunto" placeholder="<?= Traductor::t("ingresoTickets.placeholderAsunto") ?>" required>

      <label for="descripcion"><?= Traductor::t("ingresoTickets.labelDescripcion") ?></label>
      <textarea id="descripcion" name="descripcion" rows="5" placeholder="<?= Traductor::t("ingresoTickets.placeholderDescripcion") ?>" required></textarea>

      <label for="turno"><?= Traductor::t("ingresoTickets.labelTurno") ?></label>
      <select id="turno" name="turno" required>
        <option value=""><?= Traductor::t("ingresoTickets.opcionSeleccioneTurno") ?></option>
        <option value="Matutino">Matutino</option>
        <option value="Vespertino">Vespertino</option>
        <option value="Nocturno">Nocturno</option>
      </select>

      <label for="grupo"><?= Traductor::t("ingresoTickets.labelGrupo") ?></label>
      <input type="text" id="grupo" name="grupo" placeholder="<?= Traductor::t("ingresoTickets.placeholderGrupo") ?>" required>

      <label for="profesor"><?= Traductor::t("ingresoTickets.labelProfesor") ?></label>
      <input type="text" id="profesor" name="profesor" placeholder="<?= Traductor::t("ingresoTickets.placeholderProfesor") ?>" required>

      <button class="boton-principal" type="submit"><?= Traductor::t("ingresoTickets.btnEnviar") ?></button>

    </form>
  </section>
  <script src="<?= URL_BASE ?>/public/assets/js/ingresoTickets.js"></script>
  <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>
