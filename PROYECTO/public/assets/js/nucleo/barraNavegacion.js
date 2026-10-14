/**
 * Menú de la barra de navegación en pantallas chicas: abrir y cerrar la lista de enlaces.
 */

function iniciarBarraNavegacion() {
    const btnMenu = document.getElementById("btnMenu");
    const btnCerrarMenu = document.getElementById("btnCerrarMenu");
    const listaNavegacion = document.querySelector(".listaNavegacion");

    //El login no tiene barra de navegación
    if (btnMenu === null || btnCerrarMenu === null || listaNavegacion === null) {
        return;
    }

    function abrirMenu() {
        listaNavegacion.classList.add("visible");
        btnCerrarMenu.classList.add("visible");
        btnMenu.classList.add("oculto");
    }

    function cerrarMenu() {
        listaNavegacion.classList.remove("visible");
        btnCerrarMenu.classList.remove("visible");
        btnMenu.classList.remove("oculto");
    }

    btnMenu.addEventListener("click", abrirMenu);
    btnCerrarMenu.addEventListener("click", cerrarMenu);
}
