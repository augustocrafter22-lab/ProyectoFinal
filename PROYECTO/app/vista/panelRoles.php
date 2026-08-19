<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selector de rol</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/barraNavegacion.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
</head>
<body>
    <header class="BarraNavegacion">

        <nav>
            <button class="btnMenu" id="btnMenu" type="button"><img class="menu"
                    src="<?= URL_BASE ?>/public/assets/img/Bootstrap/list.svg" alt="menu" width="40"
                    height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="<?= URL_BASE ?>/public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40"
                    height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>
<section class="encabezado">
    <h1>Bienvenido, <?= htmlspecialchars($usuario->getNombre()) ?> <?= htmlspecialchars($usuario->getApellido()) ?></h1>
    <p>Seleccione el rol con el que desea ingresar</p>
</section>

<section class="botonera">
    <?php if ($_SESSION["coordinador"]): ?>
        <button class="boton-principal" type="button" onclick="location.href='<?= URL_BASE ?>/public/Administrador.php'">Coordinador</button>
    <?php endif; ?>

    <?php if ($_SESSION["tecnico"]): ?>
        <button class="boton-principal" type="button" onclick="location.href='<?= URL_BASE ?>/public/Tecnico.html'">Técnico</button>
    <?php endif; ?>

    <?php if ($_SESSION["docente"]): ?>
        <button class="boton-principal" type="button" onclick="location.href='<?= URL_BASE ?>/public/Docente.html'">Docente</button>
    <?php endif; ?>
</section>
</body>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</html>