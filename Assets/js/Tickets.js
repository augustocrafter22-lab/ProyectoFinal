const formulario = document.getElementById("ticketForm");

let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

formulario.addEventListener("submit", function(evento) {

    evento.preventDefault();

    /* Se guarda la fecha en el momento del envío */
    const fechaActual = new Date().toLocaleDateString();

    const ticket = {
        equipo: document.getElementById("equipo").value,
        laboratorio: document.getElementById("laboratorioTaller").value,
        asunto: document.getElementById("asunto").value,
        descripcion: document.getElementById("descripcion").value,
        fecha: fechaActual,
        turno: document.getElementById("turno").value,
        grupo: document.getElementById("grupo").value,
        profesor: document.getElementById("profesor").value,
        estado: "Pendiente",
        idIncidencia: "INC-" + fechaActual + "-" + (tickets.length + 1),
        prioridad: "Indefinida"
    };

    tickets.push(ticket);

    localStorage.setItem(
        "tickets",
        JSON.stringify(tickets)
    );

    console.log("Ticket creado:");
    console.log(ticket);

    console.log("Tickets guardados:");
    console.log(tickets);

    formulario.reset();
});