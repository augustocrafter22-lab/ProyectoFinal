//Muestra solo los botones de los roles que tiene habilitados el usuario
function mostrarRolesHabilitados() {
    for (const enlaceRol of document.querySelectorAll("[data-rol]")) {
        enlaceRol.hidden = !datosSesion.roles.includes(enlaceRol.dataset.rol);
    }
}

async function iniciar() {
    await iniciarPagina();
    mostrarRolesHabilitados();
}

iniciar();
