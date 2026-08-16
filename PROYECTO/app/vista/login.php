<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio de sesión</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body id="inicio">

    <header class="barraNavegacion">
        <img src="assets/imagenes/Isotipo-UTU-Color-Dorado-PNG.png" alt="Logo de la empresa" class="logo">

        <h1>S.G.R.S.I</h1>

        <nav>
            <button class="btnMenu" id="btnMenu" type="button">
                <img src="assets/imagenes/Bootstrap/list.svg" alt="Abrir menú" class="iconoMenu">
            </button>

            <button class="btnCerrarMenu" id="btnCerrarMenu" type="button">
                <img src="assets/imagenes/Bootstrap/x.svg" alt="Cerrar menú" class="iconoMenu">
            </button>

            <ul class="listaNavegacion">
                <li>
                    <a href="index.html" class="btnNavegacion">
                        Inicio
                    </a>
                </li>

                <li>
                    <a href="sobreNosotros.html" class="btnNavegacion">
                        Sobre nosotros
                    </a>
                </li>

                <li>
                    <a href="contacto.html" class="btnNavegacion">
                        Contáctanos
                    </a>
                </li>

                <li>
                    <a href="trabajaConNosotros.html" class="btnNavegacion">
                        Trabaja con nosotros
                    </a>
                </li>

                <li>
                    <a href="login.php" class="btnNavegacion">
                        Ingresar al sistema
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="seccionLogin">
            <h2>Ingreso al sistema</h2>

            <form action="procesarLogin.php" method="post">
                <fieldset>
                    <legend>Inicio de sesión</legend>

                    <div class="cajaEntradaDeDatos">
                        <label for="cedula">
                            Cédula
                        </label>

                        <input type="text" id="cedula" name="cedula" autocomplete="username" pattern="[1-9][0-9]{7}"
                            title="Ingrese exactamente 8 dígitos sin puntos ni guiones" inputmode="numeric"
                            maxlength="8" required>
                    </div>

                    <div class="cajaEntradaDeDatos">
                        <label for="clave">
                            Contraseña
                        </label>

                        <input type="password" id="clave" name="clave" autocomplete="current-password" minlength="12"
                            required>
                    </div>
                </fieldset>

                <button type="submit">
                    Iniciar sesión
                </button>
            </form>
        </section>
    </main>

    <a href="#inicio" class="btnSubir">
        <i class="bi bi-caret-up-fill"></i>
    </a>

    <footer>
        <address>
            <a href="http://instagram.com">
                @DkelanEnterprise
            </a>

            <a href="tel:+45677373">
                +4567 7373
            </a>

            <a href="tel:098318897">
                098 318 897
            </a>

            <a href="mailto:DeKlanEnterprice@gmail.com">
                DeKlanEnterprice@gmail.com
            </a>
        </address>

        <p>© 2026 Deklan Enterprise</p>
    </footer>

    <script src="../../JS/barraNavegacion.js"></script>
</body>

</html>