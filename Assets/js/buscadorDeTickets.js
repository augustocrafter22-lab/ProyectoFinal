const buscadorDeTickets = document.getElementById("buscadorDeTickets");
const buscarTicket = document.getElementById("buscarTicket");

buscarTicket.addEventListener("click", function() {

    const textoBuscado = buscadorDeTickets.value.trim();

    ticketsVista.forEach(function(ticket) {

        const idIncidencia = ticket.querySelector("h3").textContent.trim();

        if (idIncidencia === textoBuscado) {

            ticket.style.display = "flex";

        } else {

            ticket.style.display = "none";

        }

    });

});