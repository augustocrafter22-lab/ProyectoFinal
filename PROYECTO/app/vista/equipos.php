<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
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
          <li><a href="<?= URL_BASE ?>/public/Tecnico.php">Regresar</a></li>
          <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
        </ul>
      </nav>
      <h1>S.G.R.S.I</h1>
      <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75" />
    </header>

    <main>
      <section class="modulo">
        <header class="cajaEncabezado">
          <h2>Datos de Equipos</h2>
        </header>

        <section class="filtrosBarra" aria-label="Filtros de equipos">
          <label class="filtroContenedor" for="filtroID">
            ID
            <input class="filtroInput" type="text" id="filtroID" placeholder="Buscar por ID" />
          </label>
          <label class="filtroContenedor" for="filtroLab">
            Laboratorio
            <input class="filtroInput" type="text" id="filtroLab" placeholder="Buscar por laboratorio" />
          </label>
          <label class="filtroContenedor" for="filtroEstado">
            Estado
            <input class="filtroInput" type="text" id="filtroEstado" placeholder="Buscar por estado" />
          </label>
          <label class="filtroContenedor" for="filtroDisponibilidad">
            Disponibilidad
            <input class="filtroInput" type="text" id="filtroDisponibilidad" placeholder="Buscar por disponibilidad" />
          </label>
        </section>

        <p id="mensajeEquipos" role="status"></p>

        <form id="formularioEquipo">
          <fieldset>
            <legend id="leyendaFormularioEquipo">Alta de equipo</legend>
            <input type="hidden" id="modoFormularioEquipo" value="alta" />

            <label for="idEquipo">ID</label>
            <input type="text" id="idEquipo" name="idEquipo" required />

            <label for="idLaboratorio">Laboratorio</label>
            <select id="idLaboratorio" name="idLaboratorio" required>
              <option value="">Seleccione un laboratorio</option>
              <?php foreach ($laboratorios as $laboratorio): ?>
                <option value="<?= htmlspecialchars($laboratorio["idLaboratorio"]) ?>">
                  <?= htmlspecialchars($laboratorio["numeroLaboratorio"]) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label for="marca">Marca</label>
            <select id="marca" name="marca" required>
              <?php foreach (["Dell", "HP", "Lenovo", "Asus", "Acer"] as $marca): ?>
                <option value="<?= $marca ?>"><?= $marca ?></option>
              <?php endforeach; ?>
            </select>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
              <?php foreach (["Dañado", "Funcionando", "En mantenimiento", "No funciona"] as $estado): ?>
                <option value="<?= $estado ?>"><?= $estado ?></option>
              <?php endforeach; ?>
            </select>

            <label for="disponibilidad">Disponibilidad</label>
            <select id="disponibilidad" name="disponibilidad" required>
              <?php foreach (["Disponible", "No disponible"] as $disponibilidad): ?>
                <option value="<?= $disponibilidad ?>"><?= $disponibilidad ?></option>
              <?php endforeach; ?>
            </select>

            <label for="informacion">Información</label>
            <input type="text" id="informacion" name="informacion" />
            <button type="submit">Guardar equipo</button>
            <button type="button" id="btnCancelarEdicionEquipo" hidden>Cancelar</button>
          </fieldset>
        </form>

        <table>
          <caption>Listado de equipos registrados</caption>
          <thead>
            <tr>
              <th>ID</th>
              <th>Laboratorio</th>
              <th>Marca</th>
              <th>Estado</th>
              <th>Disponibilidad</th>
              <th>Información</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="cuerpoTablaPc"></tbody>
        </table>
      </section>
    </main>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/gestionPCs.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtrosEquipos.js"></script>
  </body>
</html>