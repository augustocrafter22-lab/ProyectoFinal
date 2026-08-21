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
                    src="<?= URL_BASE ?>public/assets/img/Bootstrap/list.svg" alt="menu" width="40" height="40px"></button>

            <button class="btnMenuC" id="btnMenuC" type="button">
                <img src="<?= URL_BASE ?>public/assets/img/Bootstrap/x.svg" alt="X" class="menu" width="40" height="40px">
            </button>

            <ul class="listaNavegacion">
                <li><a href="<?= URL_BASE ?>public/Tecnico.php">Regresar</a></li>
                <li><a href="<?= URL_BASE ?>public/RegistrarDiagnostico.php">Registrar diagnostico</a></li>
                <li><a href="<?= URL_BASE ?>public/RegistrarSolucion.php">Registrar solucion</a></li>
                <li><a href="<?= URL_BASE ?>public/cerrarSesion.php">Cerrar sesion</a></li>
            </ul>
        </nav>
        <h1>S.G.R.S.I</h1>
        <img src="<?= URL_BASE ?>public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo-Utu" width="75px">
    </header>


<header class="encabezado">
    <h1>Consultar Diagnósticos</h1>
    <p>Lista de todos los diagnósticos técnicos registrados en el sistema.</p>
</header>

<section class="modulo" id="filtroDiagnosticos">
    <form class="formulario" id="formFiltroDiagnosticos" method="GET" action="ConsultarDiagnostico.php">
        <label for="filtroTicket">Filtrar por ticket</label>
        <input type="text" id="filtroTicket" name="ticket" value="<?= htmlspecialchars($ticketFiltro) ?>" placeholder="Ej: INC-2026-0001">
        <button class="boton-principal" type="submit">Filtrar</button>
        <?php if ($ticketFiltro !== "") { ?>
            <a href="ConsultarDiagnostico.php">Quitar filtro</a>
        <?php } ?>
    </form>
</section>

<section class="modulo" id="consultarDiagnostico">
    <h2>
        <?= $ticketFiltro !== ""
            ? "Diagnósticos del ticket " . htmlspecialchars($ticketFiltro)
            : "Diagnósticos registrados" ?>
    </h2>

    <table id="tablaDiagnosticos" class="tabla">
        <?php if (empty($diagnosticos)) { ?>
            <tbody>
                <tr>
                    <td colspan="5" class="tabla-vacia">
                        <?= $ticketFiltro !== ""
                            ? "No hay diagnósticos registrados para el ticket " . htmlspecialchars($ticketFiltro) . "."
                            : "No hay diagnósticos registrados." ?>
                    </td>
                </tr>
            </tbody>
        <?php } else { ?>
            <thead>
                <tr>
                    <th class="tabla-th">ID</th>
                    <th class="tabla-th">Ticket</th>
                    <th class="tabla-th">Diagnóstico</th>
                    <th class="tabla-th">Fecha</th>
                    <th class="tabla-th">Técnico</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diagnosticos as $indice => $diagnostico) { ?>
                    <tr class="<?= $indice % 2 === 0 ? "tabla-fila-par" : "tabla-fila-impar" ?>">
                        <td class="tabla-td"><?= htmlspecialchars($diagnostico["idDiagnostico"]) ?></td>
                        <td class="tabla-td"><?= htmlspecialchars($diagnostico["idTicket"]) ?></td>
                        <td class="tabla-td"><?= htmlspecialchars($diagnostico["diagnostico"]) ?></td>
                        <td class="tabla-td"><?= htmlspecialchars($diagnostico["fechaDiagnostico"]) ?></td>
                        <td class="tabla-td"><?= htmlspecialchars($diagnostico["cedulaTecnico"]) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        <?php } ?>
    </table>
</section>

<script src="<?= URL_BASE ?>/public/assets/js/barraNavegacion.js"></script>
</body>
</html>