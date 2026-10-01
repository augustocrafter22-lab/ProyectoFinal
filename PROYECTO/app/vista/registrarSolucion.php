<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
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
                <li><a href="Tecnico.php">Regresar</a></li>
                <li><a href="ConsultarDiagnostico.php">Consultar diagnosticos</a></li>
                <li><a href="RegistrarIntervencion.php">Registrar intervencion</a></li>
                <li><a href="RegistrarReemplazo.php">Registrar reemplazo</a></li>
                <li><a href="RegistrarReparacion.php">Registrar reparacion</a></li>
                <li><a href="cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>
<header class="encabezado">

<h1>Registro de Soluciones</h1>
<p>Aqui se podran registrar las soluciones técnicas en base al diagnóstico realizado.</p>
</header>

    <p id="mensajeRegistrarSolucion" role="status"></p>

<section class="modulo" id="Solucion">
  <h2>Registre su resolucion</h2>

  <p id="avisoSinDiagnosticos" hidden>No hay diagnósticos registrados todavía. Registre un diagnóstico antes de cargar una solución.</p>

  <form class="formulario" id="formRegistrarSolucion">
    <label for="registrarSolucionDiagnostico">Diagnóstico</label>
    <select id="registrarSolucionDiagnostico" name="idDiagnostico" required>
      <option value="">Seleccione un diagnóstico</option>
    </select>

    <label for="registrarSolucionSolucion">Solución tecnica aplicada</label>
    <textarea id="registrarSolucionSolucion" name="solucion" rows="4" minlength="10" required></textarea>

    <button class="boton-principal" type="submit">Registrar solución</button>
  </form>
</section>
<script>
    window.cedulaTecnico = <?= json_encode($_SESSION["cedula"]) ?>;
</script>
<script src="<?= URL_BASE ?>/public/assets/js/registrarSolucion.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>