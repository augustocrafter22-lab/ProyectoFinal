<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Diagnósticos</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/Css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/Css/barraNavegacion.css">
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
                <li><a href="<?= URL_BASE ?>/public/Tecnico.php">Regresar</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarDiagnostico.php">Registrar diagnostico</a></li>
                <li><a href="<?= URL_BASE ?>/public/RegistrarSolucion.php">Registrar solucion</a></li>
                <li><a href="<?= URL_BASE ?>/public/cerrarSesion.php" class="cerrarSesion">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>


<header class="encabezado">
    <h1>Consultar Diagnósticos</h1>
    <p>Lista de todos los diagnósticos técnicos registrados en el sistema.</p>
</header>

<section class="modulo" id="filtroDiagnosticos">
    <form class="formulario" id="formFiltroDiagnosticos">
        <label for="filtroTicket">Filtrar por ticket</label>
        <input type="text" id="filtroTicket" name="ticket" placeholder="Ej: INC-2026-0001">
        <button class="boton-principal" type="submit">Filtrar</button>
        <button class="boton-principal" type="button" id="btnQuitarFiltroTicket" hidden>Quitar filtro</button>
    </form>
</section>

<section class="modulo" id="consultarDiagnostico">
    <h2 id="tituloConsultarDiagnosticos">Diagnósticos registrados</h2>

    <table id="tablaDiagnosticos" class="tabla">
        <thead hidden>
            <tr>
                <th class="tabla-th">ID</th>
                <th class="tabla-th">Ticket</th>
                <th class="tabla-th">Diagnóstico</th>
                <th class="tabla-th">Fecha</th>
                <th class="tabla-th">Técnico</th>
            </tr>
        </thead>
        <tbody id="cuerpoTablaDiagnosticos">
            <tr>
                <td colspan="5" class="tabla-vacia">Cargando diagnósticos...</td>
            </tr>
        </tbody>
    </table>
</section>

<script src="<?= URL_BASE ?>/public/assets/js/consultarDiagnostico.js"></script>
<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>