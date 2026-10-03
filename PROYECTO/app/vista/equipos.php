<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equipos</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css" />
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css" />
  </head>
  <body>
    <header class="BarraNavegacion">
      <nav>
        <button class="btnMenu" id="btnMenu" type="button">
          <img class="menu" src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40" height="40" />
        </button>
        <button class="btnMenuC" id="btnMenuC" type="button">
          <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="Cerrar menú" class="menu" width="40" height="40" />
        </button>
        <ul class="listaNavegacion">
          <li><a href="<?= URL_BASE ?>/public/Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
        </ul>
      </nav>
      <h1>S.G.R.S.I</h1>
      <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75" />
    </header>

    <main>
      <section class="modulo">
        <header class="cajaEncabezado">
          <h2><?= Traductor::t("equipos.titulo") ?></h2>
        </header>

        <section class="filtrosBarra" aria-label="Filtros de equipos">
          <label class="filtroContenedor" for="filtroID">
            <?= Traductor::t("equipos.filtroId") ?>
            <input class="filtroInput" type="text" id="filtroID" placeholder="<?= Traductor::t("equipos.filtroPlaceholderId") ?>" />
          </label>
          <label class="filtroContenedor" for="filtroLab">
            <?= Traductor::t("equipos.filtroLaboratorio") ?>
            <input class="filtroInput" type="text" id="filtroLab" placeholder="<?= Traductor::t("equipos.filtroPlaceholderLaboratorio") ?>" />
          </label>
          <label class="filtroContenedor" for="filtroEstado">
            <?= Traductor::t("equipos.filtroEstado") ?>
            <input class="filtroInput" type="text" id="filtroEstado" placeholder="<?= Traductor::t("equipos.filtroPlaceholderEstado") ?>" />
          </label>
          <label class="filtroContenedor" for="filtroDisponibilidad">
            <?= Traductor::t("equipos.filtroDisponibilidad") ?>
            <input class="filtroInput" type="text" id="filtroDisponibilidad" placeholder="<?= Traductor::t("equipos.filtroPlaceholderDisponibilidad") ?>" />
          </label>
        </section>

        <p id="mensajeEquipos" role="status"></p>

        <form id="formularioEquipo">
          <fieldset>
            <legend id="leyendaFormularioEquipo"><?= Traductor::t("equipos.leyendaAlta") ?></legend>
            <input type="hidden" id="modoFormularioEquipo" value="alta" />

            <label for="idEquipo"><?= Traductor::t("equipos.labelId") ?></label>
            <input type="text" id="idEquipo" name="idEquipo" required />

            <label for="idLaboratorio"><?= Traductor::t("equipos.labelLaboratorio") ?></label>
            <select id="idLaboratorio" name="idLaboratorio" required>
              <option value=""><?= Traductor::t("equipos.opcionSeleccioneLaboratorio") ?></option>
              <?php foreach ($laboratorios as $laboratorio): ?>
                <option value="<?= htmlspecialchars($laboratorio["idLaboratorio"]) ?>">
                  <?= htmlspecialchars($laboratorio["numeroLaboratorio"]) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label for="marca"><?= Traductor::t("equipos.labelMarca") ?></label>
            <select id="marca" name="marca" required>
              <?php foreach (["Dell", "HP", "Lenovo", "Asus", "Acer"] as $marca): ?>
                <option value="<?= $marca ?>"><?= $marca ?></option>
              <?php endforeach; ?>
            </select>

            <label for="estado"><?= Traductor::t("equipos.labelEstado") ?></label>
            <select id="estado" name="estado" required>
              <?php foreach (["Dañado", "Funcionando", "En mantenimiento", "No funciona"] as $estado): ?>
                <option value="<?= $estado ?>"><?= $estado ?></option>
              <?php endforeach; ?>
            </select>

            <label for="disponibilidad"><?= Traductor::t("equipos.labelDisponibilidad") ?></label>
            <select id="disponibilidad" name="disponibilidad" required>
              <?php foreach (["Disponible", "No disponible"] as $disponibilidad): ?>
                <option value="<?= $disponibilidad ?>"><?= $disponibilidad ?></option>
              <?php endforeach; ?>
            </select>

            <label for="informacion"><?= Traductor::t("equipos.labelInformacion") ?></label>
            <input type="text" id="informacion" name="informacion" />
            <button type="submit"><?= Traductor::t("equipos.btnGuardar") ?></button>
            <button type="button" id="btnCancelarEdicionEquipo" hidden><?= Traductor::t("equipos.btnCancelar") ?></button>
          </fieldset>
        </form>

        <table>
          <caption><?= Traductor::t("equipos.captionTabla") ?></caption>
          <thead>
            <tr>
              <th><?= Traductor::t("equipos.thId") ?></th>
              <th><?= Traductor::t("equipos.thLaboratorio") ?></th>
              <th><?= Traductor::t("equipos.thMarca") ?></th>
              <th><?= Traductor::t("equipos.thEstado") ?></th>
              <th><?= Traductor::t("equipos.thDisponibilidad") ?></th>
              <th><?= Traductor::t("equipos.thInformacion") ?></th>
              <th><?= Traductor::t("equipos.thAcciones") ?></th>
            </tr>
          </thead>
          <tbody id="cuerpoTablaPc"></tbody>
        </table>
      </section>
    </main>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/respuestaAPI.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/gestionPCs.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtrosEquipos.js"></script>
  </body>
</html>