let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

const listaTickets = document.getElementById("listaTickets");

tickets.forEach(function(ticket, indice) {

    const article = document.createElement("article");
    article.classList.add("ticket");


    const ticketInfo = document.createElement("section");
    ticketInfo.classList.add("ticketInfo");

    const idIncidencia = document.createElement("h3");
    const enlace = document.createElement("a");
    enlace.href = "ConsultarDiagnostico.html?ticket=" + ticket.idIncidencia;
    enlace.textContent = ticket.idIncidencia;
    enlace.className = "ticket-enlace";
    idIncidencia.appendChild(enlace);

    const asunto = document.createElement("p");
    asunto.textContent = ticket.asunto;

    const equipo = document.createElement("p");
    equipo.textContent = ticket.equipo;

    ticketInfo.appendChild(idIncidencia);
    ticketInfo.appendChild(asunto);
    ticketInfo.appendChild(equipo);


    const ticketEstado = document.createElement("section");
    ticketEstado.classList.add("ticketEstado");

    const selectEstado = document.createElement("select");
    selectEstado.className = "select-estado";
    ["Pendiente", "En Proceso", "Resuelto", "Cerrado"].forEach(function(opcion) {
        const opt = document.createElement("option");
        opt.value = opcion;
        opt.textContent = opcion;
        if (ticket.estado === opcion) {
            opt.selected = true;
        }
        selectEstado.appendChild(opt);
    });

    selectEstado.addEventListener("change", function() {
        tickets[indice].estado = selectEstado.value;
        article.dataset.estado = selectEstado.value; // actualiza el data attribute al cambiar
        localStorage.setItem("tickets", JSON.stringify(tickets));
    });

    const selectPrioridad = document.createElement("select");
    selectPrioridad.className = "select-prioridad";
    ["Indefinida", "Alta", "Media", "Baja"].forEach(function(opcion) {
        const opt = document.createElement("option");
        opt.value = opcion;
        opt.textContent = "Prioridad: " + opcion;
        if (ticket.prioridad === opcion) {
            opt.selected = true;
        }
        selectPrioridad.appendChild(opt);
    });

    selectPrioridad.addEventListener("change", function() {
        tickets[indice].prioridad = selectPrioridad.value;
        article.dataset.prioridad = selectPrioridad.value; // actualiza el data attribute al cambiar
        localStorage.setItem("tickets", JSON.stringify(tickets));
    });

    const laboratorio = document.createElement("p");
    laboratorio.classList.add("laboratorio");
    laboratorio.textContent = ticket.laboratorio;

    ticketEstado.appendChild(selectEstado);
    ticketEstado.appendChild(selectPrioridad);
    ticketEstado.appendChild(laboratorio);

    article.appendChild(ticketInfo);
    article.appendChild(ticketEstado);

    listaTickets.appendChild(article);

});