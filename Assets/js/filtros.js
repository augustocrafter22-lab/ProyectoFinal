const filtroPrioridad = document.getElementById("filtroPrioridad");
const ticketsVista = document.querySelectorAll(".ticket");

filtroPrioridad.addEventListener("change", function() {

    const prioridadSeleccionada = filtroPrioridad.value;

    ticketsVista.forEach(function(ticket) {

        const textoTicket = ticket.textContent;

        if (prioridadSeleccionada === "" || textoTicket.includes(prioridadSeleccionada)) {
            ticket.style.display = "flex";
        } else {
            ticket.style.display = "none";
        }
    });
});

const filtroEstado = document.getElementById("filtroEstado");

filtroEstado.addEventListener("change", function() {

    const estadoSeleccionado = filtroEstado.value;

    ticketsVista.forEach(function(ticket) {

        const textoTicket = ticket.textContent;

        if (estadoSeleccionado === "" || textoTicket.includes(estadoSeleccionado)) {
            ticket.style.display = "flex";
        } else {
            ticket.style.display = "none";
        }
    });
});
