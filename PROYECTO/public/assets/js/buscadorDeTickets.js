const buscadorDeTickets = document.getElementById("buscadorDeTickets");
const buscarTicket = document.getElementById("buscarTicket");

function filtrarPorIdTicket() {

    const textoBuscado = buscadorDeTickets.value.trim().toLowerCase();

    const ticketsVista = document.querySelectorAll(".ticket");

    ticketsVista.forEach(function(ticket) {

        const idIncidencia = ticket.dataset.id.toLowerCase();

        if (textoBuscado === "" || idIncidencia.includes(textoBuscado)) {
            ticket.style.display = "flex";
        } else {
            ticket.style.display = "none";
        }

    });

}

buscarTicket.addEventListener("click", filtrarPorIdTicket);
