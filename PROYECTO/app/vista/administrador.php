<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
        exit;
}
require_once RUTA_CONTROLADOR . "/cargarAdministrador.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrador</title>

   <link rel="stylesheet" href="../../Css/style.css">

    <link rel="stylesheet" href="../../Css/Formulario.css">
</head>

<body id="inicio">

    <header class="barraNavegacion">
        <img src="../../Imagenes/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo de la empresa" class="logo">

        <h1>S.G.R.S.I</h1>

        <nav>
            <button class="btnMenu" id="btnMenu" type="button">
                <img src="../../Imagenes/Bootstrap/list.svg" alt="Abrir menú" class="iconoMenu">
            </button>

            <button class="btnCerrarMenu" id="btnCerrarMenu" type="button">
                <img src="../../Imagenes/Bootstrap/x.svg" alt="Cerrar menú" class="iconoMenu">
            </button>

            <ul class="listaNavegacion">
                <li>
                    <a href="cerrarSesion.php" class="btnNavegacion">
                        Cerrar sesión
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="seccionTablaEmpleados">
            <header class="cajaEncabezado">
                <h2>Datos de empleados</h2>

                <button type="button" class="btnOperacion" id="btnAltaEmpleado">
                    Alta de empleado
                </button>
            </header>

            <table>
                <caption>
                    Listado de registrados
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Cédula</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellido</th>
                        <th scope="col">Cargo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpoTablaEmpleados">
                </tbody>
            </table>
        </section>

        <dialog id="dialogGestionarEmpleado" class="dialogGestionarEmpleado seccionFormulario">
            <button class="btnCerrarGestionarEmpleado" id="btnCerrarGestionarEmpleado" type="button">
                <img src="../../Imagenes/Bootstrap/x.svg" alt="Cerrar formulario" class="iconoMenu">
            </button>

          <form action="procesarAltaUsuario.php" method="post" id="formularioGestionarEmpleado">
                <fieldset>
                    <legend>
                        Gestión de empleado
                    </legend>

                    <fieldset>
                        <legend>
                            Datos del empleado
                        </legend>

                        <div class="cajaEntradaDeDatos">
                            <label for="cedula">
                                Cédula
                            </label>

                            <input type="text" id="cedula" name="cedula" placeholder="Ingrese la cédula"
                                autocomplete="off" pattern="[1-9][0-9]{7}"
                                title="Ingrese exactamente 8 dígitos sin puntos ni guiones" inputmode="numeric"
                                maxlength="8" required>
                        </div>

                        <div class="cajaEntradaDeDatos">
                            <label for="nombre">
                                Nombre
                            </label>

                            <input type="text" id="nombre" name="nombre" placeholder="Ingrese el nombre"
                                autocomplete="given-name" required>
                        </div>

                        <div class="cajaEntradaDeDatos">
                            <label for="apellido">
                                Apellido
                            </label>

                            <input type="text" id="apellido" name="apellido" placeholder="Ingrese el apellido"
                                autocomplete="family-name" required>
                        </div>

                        <div class="cajaEntradaDeDatos">
                            <label for="cargo">
                                Cargo
                            </label>

                            <select name="cargo" id="cargo" required>
                                <option value="" disabled selected>
                                    Seleccione un cargo
                                </option>

                                <option value="Transporte">
                                    Transporte
                                </option>

                                <option value="Gestión">
                                    Gestión
                                </option>

                                <option value="Administrativo">
                                    Administrativo
                                </option>

                                <option value="Depósito">
                                    Depósito
                                </option>

                                <option value="Logística">
                                    Logística
                                </option>
                            </select>
                        </div>
                    </fieldset>

                    <button type="submit">
                        Guardar
                    </button>
                </fieldset>
            </form>
        </dialog>
    </main>

    <a href="#inicio" class="btnSubir">
        <i class="bi bi-caret-up-fill"></i>
    </a>

    <footer>
        <p>
            S.G.R.S.I
        </p>

        <p>
            © 2026 Deklan Enterprise
        </p>
    </footer>

    <script src="../../JS/barraNavegacion.js"></script>
    <script src="../../JS/Editor.js"></script>
</body>

</html>