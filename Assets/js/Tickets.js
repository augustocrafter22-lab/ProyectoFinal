const formulario = document.getElementById("ticketForm");

let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

formulario.addEventListener("submit", function(evento) {

    evento.preventDefault();

    const fechaActual = new Date().toLocaleDateString();
    const idIncidencia = "INC-" + new Date().getFullYear() + "-" + String(tickets.length + 1).padStart(4, "0");
    const ticket = {
        idIncidencia: idIncidencia,
        equipo: document.getElementById("equipo").value,
        laboratorio: document.getElementById("laboratorioTaller").value,
        asunto: document.getElementById("asunto").value,
        descripcion: document.getElementById("descripcion").value,
        fecha: fechaActual,
        fechaFiltro: new Date().toISOString().split("T")[0],
        turno: document.getElementById("turno").value,
        grupo: document.getElementById("grupo").value,
        profesor: document.getElementById("profesor").value,
        estado: "Pendiente",
        prioridad: "Indefinida"
    };

    tickets.push(ticket);

    localStorage.setItem(
        "tickets",
        JSON.stringify(tickets)
    );

    console.log(ticket);
    console.log(tickets);

    formulario.reset();

});