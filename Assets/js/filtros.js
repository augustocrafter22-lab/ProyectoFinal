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
