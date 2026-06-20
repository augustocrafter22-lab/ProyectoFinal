function aplicarFiltros() {

    const prioridadSeleccionada = filtroPrioridad.value;
    const estadoSeleccionado = filtroEstado.value;

    const ticketsVista = document.querySelectorAll(".ticket");

    ticketsVista.forEach(function(article) {

        const selectEstado = article.querySelector(".select-estado");
        const selectPrioridad = article.querySelector(".select-prioridad");

        const coincideEstado =
            estadoSeleccionado === "" ||
            selectEstado.value === estadoSeleccionado;

        const coincidePrioridad =
            prioridadSeleccionada === "" ||
            selectPrioridad.value === prioridadSeleccionada;

        if (coincideEstado && coincidePrioridad) {
            article.style.display = "flex";
        } else {
            article.style.display = "none";
        }

    });
}

filtroPrioridad.addEventListener("change", aplicarFiltros);
filtroEstado.addEventListener("change", aplicarFiltros);