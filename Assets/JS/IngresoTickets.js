let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

const listaTickets = document.getElementById("listaTickets");

tickets.forEach((ticket, indice) => {

    const article = document.createElement("article");
    article.classList.add("ticket");
    article.dataset.fecha = ticket.fechaFiltro;

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

    ["Pendiente", "En Proceso", "Resuelto", "Cerrado"].forEach(opcion => {

        const opt = document.createElement("option");

        opt.value = opcion;
        opt.textContent = opcion;

        if (ticket.estado === opcion) {
            opt.selected = true;
        }

        selectEstado.appendChild(opt);

    });

    selectEstado.addEventListener("change", () => {

        tickets[indice].estado = selectEstado.value;
        article.dataset.estado = selectEstado.value;

        localStorage.setItem(
            "tickets",
            JSON.stringify(tickets)
        );

    });

    const selectPrioridad = document.createElement("select");
    selectPrioridad.className = "select-prioridad";

    ["Indefinida", "Alta", "Media", "Baja"].forEach(opcion => {

        const opt = document.createElement("option");

        opt.value = opcion;
        opt.textContent = "Prioridad: " + opcion;

        if (ticket.prioridad === opcion) {
            opt.selected = true;
        }

        selectPrioridad.appendChild(opt);

    });

    selectPrioridad.addEventListener("change", () => {

        tickets[indice].prioridad = selectPrioridad.value;
        article.dataset.prioridad = selectPrioridad.value;

        localStorage.setItem(
            "tickets",
            JSON.stringify(tickets)
        );

    });

    const laboratorio = document.createElement("p");
    laboratorio.classList.add("laboratorio");
    laboratorio.textContent = ticket.laboratorio;

    const btnFinalizar = document.createElement("button");
    btnFinalizar.textContent = "Finalizar Ticket";
    btnFinalizar.classList.add("btn-finalizar");

    btnFinalizar.addEventListener("click", () => {

        tickets[indice].estado = "Resuelto";
        tickets[indice].fechaFinalizacion = new Date().toLocaleDateString();

        localStorage.setItem(
            "tickets",
            JSON.stringify(tickets)
        );

        location.reload();

    });

    ticketEstado.appendChild(selectEstado);
    ticketEstado.appendChild(selectPrioridad);
    ticketEstado.appendChild(laboratorio);
    ticketEstado.appendChild(btnFinalizar);

    if (ticket.fechaFinalizacion) {
        const fechaFinalizacion = document.createElement("p");
        fechaFinalizacion.textContent = "Finalizado: " + ticket.fechaFinalizacion;
        ticketEstado.appendChild(fechaFinalizacion);
    }

    article.appendChild(ticketInfo);
    article.appendChild(ticketEstado);

    listaTickets.appendChild(article);

});