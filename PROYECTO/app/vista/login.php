<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.G.R.S.I.</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/assets/css/login.css">
</head>

<body>
    <main>
        <section class="SGRSI">
            <img src="<?= URL_BASE ?>/public/assets/img/Isotipo-UTU-Color-Dorado-PNG.png" alt="DeKlan Enterprise" class="logo" width="200px" height="200px" />
            <h1>S.G.R.S.I.</h1>
            <p>Sistema de Gestión de Recursos y Soporte de Informática</p>
        </section>
        <section class="seccionLogin">
            <form action="<?= URL_BASE ?>/app/controlador/procesarLogin.php" method="POST" class="login-form" id="loginForm">
                <h2>Iniciar Sesión</h2>
                <fieldset>
                    <label for="username">CI:</label>
                    <input type="text" id="username" name="username" minlength="7" maxlength="8" required />

                    <label for="clave">Contraseña:</label>
                    <input type="password" id="clave" name="clave" autocomplete="current-password" minlength="1" required />

                    <p id="errorMessage" style="color: red"></p>
                    <button type="submit" class="boton-principal">Ingresar</button>
                </fieldset>
            </form>
        </section>
    </main>

   
</body>
</html>
