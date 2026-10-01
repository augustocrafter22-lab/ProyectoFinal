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
            <p><?= Traductor::t("login.subtitulo") ?></p>
        </section>
        <section class="seccionLogin">
            <p class="selectorIdioma">
                <a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=es"><?= Traductor::t("common.idiomaEs") ?></a>
                |
                <a href="<?= URL_BASE ?>/public/cambiarIdioma.php?idioma=en"><?= Traductor::t("common.idiomaEn") ?></a>
            </p>
            <form action="<?= URL_BASE ?>/public/procesarLogin.php" method="POST" class="login-form" id="loginForm">
                <h2><?= Traductor::t("login.titulo") ?></h2>
                <fieldset>
                    <label for="username"><?= Traductor::t("login.labelCi") ?></label>
                    <input type="text" id="username" name="username" minlength="7" maxlength="8" required />

                    <label for="clave"><?= Traductor::t("login.labelClave") ?></label>
                    <input type="password" id="clave" name="clave" autocomplete="current-password" minlength="1" required />

                    <?php if (isset($_GET["error"])): ?>
                        <p id="errorMessage" style="color: red"><?= htmlspecialchars($_GET["error"]) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="boton-principal"><?= Traductor::t("login.botonIngresar") ?></button>
                </fieldset>
            </form>
        </section>
    </main>

   
</body>
</html>
