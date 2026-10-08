<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="<?= Token::generarTokenCSRF() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Préstamos</title>
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
          <li><a href="<?= URL_BASE ?>/public/paginas/Tecnico.php"><?= Traductor::t("common.regresar") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/paginas/cerrarSesion.php" class="cerrarSesion"><?= Traductor::t("common.cerrarSesion") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a></li>
          <li><a href="<?= URL_BASE ?>/public/paginas/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a></li>
        </ul>
      </nav>
      <h1>S.G.R.S.I</h1>
      <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75" />
    </header>

    <main
      data-texto-devolver="<?= htmlspecialchars(Traductor::t("prestamos.btnDevolver")) ?>"
      data-texto-eliminar="<?= htmlspecialchars(Traductor::t("prestamos.btnEliminar")) ?>"
      data-texto-estado-activo="<?= htmlspecialchars(Traductor::t("prestamos.estadoActivo")) ?>"
      data-texto-estado-devuelto="<?= htmlspecialchars(Traductor::t("prestamos.estadoDevuelto")) ?>"
      data-texto-sin-equipos="<?= htmlspecialchars(Traductor::t("prestamos.sinEquiposDisponibles")) ?>"
      data-texto-error-conexion="<?= htmlspecialchars(Traductor::t("prestamos.errorConexion")) ?>"
    >
      <section class="modulo">
        <header class="cajaEncabezado">
          <h2><?= Traductor::t("prestamos.titulo") ?></h2>
        </header>

        <section class="filtrosBarra" aria-label="Filtros de préstamos">
          <label class="filtroContenedor" for="filtroEstadoPrestamo">
            <?= Traductor::t("prestamos.filtroEstado") ?>
            <select class="filtroInput" id="filtroEstadoPrestamo">
              <option value=""><?= Traductor::t("prestamos.opcionTodos") ?></option>
              <option value="Activo"><?= Traductor::t("prestamos.estadoActivo") ?></option>
              <option value="Devuelto"><?= Traductor::t("prestamos.estadoDevuelto") ?></option>
            </select>
          </label>
        </section>

        <p id="mensajePrestamos" role="status"></p>

        <form id="formularioPrestamo">
          <fieldset>
            <legend><?= Traductor::t("prestamos.leyendaAlta") ?></legend>

            <label for="idEquipo"><?= Traductor::t("prestamos.labelEquipo") ?></label>
            <select id="idEquipo" name="idEquipo" required>
              <option value=""><?= Traductor::t("prestamos.opcionSeleccioneEquipo") ?></option>
            </select>

            <label for="cedulaSolicitante"><?= Traductor::t("prestamos.labelSolicitante") ?></label>
            <input
              type="text"
              id="cedulaSolicitante"
              name="cedulaSolicitante"
              placeholder="<?= htmlspecialchars(Traductor::t("prestamos.placeholderSolicitante")) ?>"
              pattern="[0-9]{8}"
              inputmode="numeric"
              maxlength="8"
              required
            />

            <label for="fechaDevolucionEstimada"><?= Traductor::t("prestamos.labelFechaEstimada") ?></label>
            <input type="date" id="fechaDevolucionEstimada" name="fechaDevolucionEstimada" required />

            <button type="submit"><?= Traductor::t("prestamos.btnRegistrar") ?></button>
          </fieldset>
        </form>

        <table>
          <caption><?= Traductor::t("prestamos.captionTabla") ?></caption>
          <thead>
            <tr>
              <th><?= Traductor::t("prestamos.thId") ?></th>
              <th><?= Traductor::t("prestamos.thEquipo") ?></th>
              <th><?= Traductor::t("prestamos.thSolicitante") ?></th>
              <th><?= Traductor::t("prestamos.thFechaPrestamo") ?></th>
              <th><?= Traductor::t("prestamos.thFechaEstimada") ?></th>
              <th><?= Traductor::t("prestamos.thFechaReal") ?></th>
              <th><?= Traductor::t("prestamos.thEstado") ?></th>
              <th><?= Traductor::t("prestamos.thAcciones") ?></th>
            </tr>
          </thead>
          <tbody id="cuerpoTablaPrestamos"></tbody>
        </table>
      </section>
    </main>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/respuestaAPI.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/gestionPrestamos.js"></script>
  </body>
</html>
