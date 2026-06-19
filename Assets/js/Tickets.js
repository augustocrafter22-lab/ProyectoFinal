const formulario = document.getElementById("ticketForm");

formulario.addEventListener("submit", function (evento) {
  evento.preventDefault();
let tickets = JSON.parse(localStorage.getItem("tickets")) || [];

formulario.addEventListener("submit", function(evento) {

  /* Se captura la fecha en el momento del envío para reflejar
       el instante exacto del reporte, no una fecha posterior. */
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
    /* "Pendiente" es el estado inicial por defecto; cambia
           conforme avanza el flujo de diagnóstico/solución. */
    estado: "Pendiente",
  };

  console.log(ticket);

  formulario.reset();
});
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
