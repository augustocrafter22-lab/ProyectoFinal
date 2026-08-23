<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equipos</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css" />
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css" />
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/equipos.css" />
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/Formulario.css" />
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/globalSistema.css" />
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
          <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php">Cerrar sesion</a></li>
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

        <?php if (isset($_GET["error"])): ?>
          <p style="color: red"><?= htmlspecialchars($_GET["error"]) ?></p>
        <?php elseif (isset($_GET["exito"])): ?>
          <p style="color: green"><?= htmlspecialchars($_GET["exito"]) ?></p>
        <?php endif; ?>

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

        <form action="<?= URL_BASE ?>/public/procesarEquipo.php" method="POST">
          <fieldset>
            <legend><?= $equipoEditar === null ? "Alta de equipo" : "Modificar equipo" ?></legend>
            <input type="hidden" name="accion" value="<?= $equipoEditar === null ? "alta" : "modificar" ?>" />

            <label for="idEquipo">ID</label>
            <input type="text" id="idEquipo" name="idEquipo" value="<?= htmlspecialchars($equipoEditar["idEquipo"] ?? "") ?>" required <?= $equipoEditar !== null ? "readonly" : "" ?> />

            <label for="idLaboratorio">Laboratorio</label>
            <select id="idLaboratorio" name="idLaboratorio" required>
              <option value="">Seleccione un laboratorio</option>
              <?php foreach ($laboratorios as $laboratorio): ?>
                <option value="<?= htmlspecialchars($laboratorio["idLaboratorio"]) ?>" <?= ($equipoEditar["idLaboratorio"] ?? "") === $laboratorio["idLaboratorio"] ? "selected" : "" ?>>
                  <?= htmlspecialchars($laboratorio["numeroLaboratorio"]) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label for="marca">Marca</label>
            <select id="marca" name="marca" required>
              <?php foreach (["Dell", "HP", "Lenovo", "Asus", "Acer"] as $marca): ?>
                <option value="<?= $marca ?>" <?= ($equipoEditar["marca"] ?? "") === $marca ? "selected" : "" ?>><?= $marca ?></option>
              <?php endforeach; ?>
            </select>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
              <?php foreach (["Dañado", "Funcionando", "En mantenimiento", "No funciona"] as $estado): ?>
                <option value="<?= $estado ?>" <?= ($equipoEditar["estado"] ?? "") === $estado ? "selected" : "" ?>><?= $estado ?></option>
              <?php endforeach; ?>
            </select>

            <label for="disponibilidad">Disponibilidad</label>
            <select id="disponibilidad" name="disponibilidad" required>
              <?php foreach (["Disponible", "No disponible"] as $disponibilidad): ?>
                <option value="<?= $disponibilidad ?>" <?= ($equipoEditar["disponibilidad"] ?? "") === $disponibilidad ? "selected" : "" ?>><?= $disponibilidad ?></option>
              <?php endforeach; ?>
            </select>

            <label for="informacion">Información</label>
            <input type="text" id="informacion" name="informacion" value="<?= htmlspecialchars($equipoEditar["informacion"] ?? "") ?>" />
            <button type="submit">Guardar equipo</button>
            <?php if ($equipoEditar !== null): ?>
              <a href="<?= URL_BASE ?>/public/Equipos.php">Cancelar</a>
            <?php endif; ?>
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
          <tbody id="cuerpoTablaPc">
            <?php foreach ($equipos as $equipo): ?>
              <tr>
                <td><?= htmlspecialchars($equipo["idEquipo"]) ?></td>
                <td><?= htmlspecialchars($equipo["laboratorio"]) ?></td>
                <td><?= htmlspecialchars($equipo["marca"]) ?></td>
                <td><?= htmlspecialchars($equipo["estado"]) ?></td>
                <td><?= htmlspecialchars($equipo["disponibilidad"]) ?></td>
                <td><?= htmlspecialchars($equipo["informacion"] ?? "") ?></td>
                <td>
                  <a href="<?= URL_BASE ?>/public/Equipos.php?editar=<?= urlencode($equipo["idEquipo"]) ?>">Modificar</a>
                  <form action="<?= URL_BASE ?>/public/procesarEquipo.php" method="POST">
                    <input type="hidden" name="accion" value="baja" />
                    <input type="hidden" name="idEquipo" value="<?= htmlspecialchars($equipo["idEquipo"]) ?>" />
                    <button type="submit">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>

    <script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
    <script src="<?= URL_BASE ?>/public/assets/js/filtrosEquipos.js"></script>
  </body>
</html>