let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

const listaTickets = document.getElementById("listaTickets");

tickets.forEach(function(ticket) {

    const article = document.createElement("article");
    article.classList.add("ticket");

    const ticketInfo = document.createElement("section");
    ticketInfo.classList.add("ticketInfo");

    const idIncidencia = document.createElement("h3");
    idIncidencia.textContent = ticket.idIncidencia;

    const asunto = document.createElement("p");
    asunto.textContent = ticket.asunto;

    const equipo = document.createElement("p");
    equipo.textContent = ticket.equipo;

    ticketInfo.appendChild(idIncidencia);
    ticketInfo.appendChild(asunto);
    ticketInfo.appendChild(equipo);

    const ticketEstado = document.createElement("section");
    ticketEstado.classList.add("ticketEstado");

    const estado = document.createElement("p");
    estado.classList.add("estado");
    estado.textContent = ticket.estado;

    const prioridad = document.createElement("p");
    prioridad.classList.add("prioridad");
    prioridad.textContent = "Prioridad: " + ticket.prioridad;

    const laboratorio = document.createElement("p");
    laboratorio.classList.add("laboratorio");
    laboratorio.textContent = ticket.laboratorio;

    ticketEstado.appendChild(estado);
    ticketEstado.appendChild(prioridad);
    ticketEstado.appendChild(laboratorio);

    article.appendChild(ticketInfo);
    article.appendChild(ticketEstado);

    listaTickets.appendChild(article);

});