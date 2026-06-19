let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

console.log(tickets);

const listaTickets = document.getElementById("listaTickets");

tickets.forEach(function(ticket) {

    const article = document.createElement("article");
    article.classList.add("ticket");

    const laboratorio = document.createElement("p");
    laboratorio.textContent = "Laboratorio: " + ticket.laboratorio;

    const equipo = document.createElement("p");
    equipo.textContent = "Equipo: " + ticket.equipo;

    const asunto = document.createElement("p");
    asunto.textContent = "Asunto: " + ticket.asunto;

    const descripcion = document.createElement("p");
    descripcion.textContent = "Descripción: " + ticket.descripcion;

    const turno = document.createElement("p");
    turno.textContent = "Turno: " + ticket.turno;

    const grupo = document.createElement("p");
    grupo.textContent = "Grupo: " + ticket.grupo;

    const fecha = document.createElement("p");
    fecha.textContent = "Fecha: " + ticket.fecha;

    const profesor = document.createElement("p");
    profesor.textContent = "Profesor: " + ticket.profesor;

    const estado = document.createElement("p");
    estado.textContent = "Estado: " + ticket.estado;

    article.appendChild(laboratorio);
    article.appendChild(equipo);
    article.appendChild(asunto);
    article.appendChild(descripcion);
    article.appendChild(turno);
    article.appendChild(grupo);
    article.appendChild(fecha);
    article.appendChild(profesor);
    article.appendChild(estado);

    listaTickets.appendChild(article);

});