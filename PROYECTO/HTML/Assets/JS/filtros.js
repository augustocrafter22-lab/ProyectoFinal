const filtroPrioridad = document.getElementById("filtroPrioridad");
const filtroEstado = document.getElementById("filtroEstado");
const filtroDeEquipos = document.getElementById("filtroDeEquipos");

function aplicarFiltros() {

    const prioridadSeleccionada = filtroPrioridad.value;
    const estadoSeleccionado = filtroEstado.value;
    const equipoSeleccionado = filtroDeEquipos.value;

    const ticketsVista = document.querySelectorAll(".ticket");

        ticketsVista.forEach(article => {
        const selectEstado = article.querySelector(".select-estado");
        const selectPrioridad = article.querySelector(".select-prioridad");

        const coincideEstado =
            estadoSeleccionado === "" ||
            selectEstado.value === estadoSeleccionado;

        const coincidePrioridad =
            prioridadSeleccionada === "" ||
            selectPrioridad.value === prioridadSeleccionada;

        const coincideEquipo =
            equipoSeleccionado === "" ||
            article.textContent.includes(equipoSeleccionado);

        if (coincideEstado && coincidePrioridad && coincideEquipo) {

            article.style.display = "flex";

        } else {

            article.style.display = "none";

        }

    });

}

filtroPrioridad.addEventListener("change", aplicarFiltros);
filtroEstado.addEventListener("change", aplicarFiltros);
filtroDeEquipos.addEventListener("change", aplicarFiltros);