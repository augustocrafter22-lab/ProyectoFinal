const formulario = document.getElementById("ticketForm");

let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

formulario.addEventListener("submit", function(evento) {

    evento.preventDefault();

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
        estado: "Pendiente"
    };

    tickets.push(ticket);

    localStorage.setItem(
        "tickets",
        JSON.stringify(tickets)
    );

    formulario.reset();

});