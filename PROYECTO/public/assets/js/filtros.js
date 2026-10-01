const filtroPrioridad = document.getElementById("filtroPrioridad");
const filtroEstado = document.getElementById("filtroEstado");
const filtroEquipo = document.getElementById("filtroDeEquipos");
const buscadorDeTickets = document.getElementById("buscadorDeTickets");
const buscarTicket = document.getElementById("buscarTicket");
const fechaDesde = document.getElementById("fechaDesde");
const fechaHasta = document.getElementById("fechaHasta");
const filtrarFechas = document.getElementById("filtrarFechas");

// Aplica todos los filtros de la vista a la vez (estado, prioridad, equipo,
// búsqueda por id y rango de fechas), así un filtro no pisa a los otros.
function aplicarFiltros() {

    const prioridadSeleccionada = filtroPrioridad.value;
    const estadoSeleccionado = filtroEstado.value;
    const equipoSeleccionado = filtroEquipo.value;
    const textoBuscado = buscadorDeTickets.value.trim().toLowerCase();
    const desde = fechaDesde.value;
    const hasta = fechaHasta.value;

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
            article.dataset.equipo === equipoSeleccionado;

        const coincideBusqueda =
            textoBuscado === "" ||
            article.dataset.id.toLowerCase().includes(textoBuscado);

        // fechaCreacion llega como "AAAA-MM-DD HH:MM:SS", se toma solo el día.
        // Se comparan los textos "AAAA-MM-DD" en lugar de objetos Date, así no
        // influye la zona horaria y el día "hasta" queda incluido completo.
        // Si una de las fechas está vacía, ese extremo del rango no se aplica.
        const diaTicket = article.dataset.fecha.split(" ")[0];

        const coincideFechas =
            (desde === "" || diaTicket >= desde) &&
            (hasta === "" || diaTicket <= hasta);

        if (coincideEstado && coincidePrioridad && coincideEquipo && coincideBusqueda && coincideFechas) {

            article.style.display = "flex";

        } else {

            article.style.display = "none";

        }

    });

}

filtroPrioridad.addEventListener("change", aplicarFiltros);
filtroEstado.addEventListener("change", aplicarFiltros);
filtroEquipo.addEventListener("change", aplicarFiltros);
buscarTicket.addEventListener("click", aplicarFiltros);
filtrarFechas.addEventListener("click", aplicarFiltros);
