/**
 * Arranque común de todas las pantallas.
 *
 * Cada pantalla le indica lo que necesita con atributos del <body>:
 *   data-roles="tecnico,coordinador"   roles que pueden entrar (sin este atributo no se pide sesión, como en el login)
 *   data-secciones="vistaLab"          secciones de texto que solo usa el JS de la pantalla (opcional)
 *
 * Uso en el JS de la pantalla:   await iniciarPagina();
 */

async function iniciarPagina() {
    try {
        await verificarAcceso();
        await cargarTraducciones(obtenerSeccionesDePagina());
    } catch (error) {
        document.body.classList.remove("cargando");
        window.alert(error.message);

        //Si no se pudo preparar la pantalla, el resto del código de la pantalla no se ejecuta
        return new Promise(() => {});
    }

    aplicarTraducciones();
    mostrarDatosUsuario();
    iniciarBarraNavegacion();
    iniciarBotonesComunes();

    document.body.classList.remove("cargando");

    return datosSesion;
}

//Verifica que haya sesión y que el usuario tenga uno de los roles de la pantalla
async function verificarAcceso() {
    const roles = (document.body.dataset.roles ?? "").split(",").filter(Boolean);

    if (roles.length === 0) {
        return;
    }

    await cargarSesion();

    if (!tieneAlgunRol(roles)) {
        await irALogin("sinAutorizacion");
    }
}

//Completa los elementos <span data-usuario="nombreCompleto"> con los datos de la sesión
function mostrarDatosUsuario() {
    if (datosSesion === null) {
        return;
    }

    for (const elemento of document.querySelectorAll("[data-usuario='nombreCompleto']")) {
        elemento.textContent = `${datosSesion.nombre} ${datosSesion.apellido}`;
    }
}

//Conecta los botones que están en la barra de navegación de todas las pantallas
function iniciarBotonesComunes() {
    const btnCerrarSesion = document.getElementById("btnCerrarSesion");

    if (btnCerrarSesion !== null) {
        btnCerrarSesion.addEventListener("click", cerrarSesion);
    }

    for (const btnIdioma of document.querySelectorAll("[data-idioma]")) {
        btnIdioma.addEventListener("click", () => cambiarIdioma(btnIdioma.dataset.idioma));
    }
}
