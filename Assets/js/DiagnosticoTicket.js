document.addEventListener("DOMContentLoaded", iniciarDiagnosticoTicket);

function iniciarDiagnosticoTicket() {
  cargarSelectTickets("#DiagnosticoTicketTicket");

  const formulario = document.querySelector("#formDiagnosticoTicket");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", asociarDiagnosticoTicket);
}

function asociarDiagnosticoTicket(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#DiagnosticoTicketTicket").value;
  const texto = document.querySelector("#DiagnosticoTicketDiagnostico").value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket.");
    return;
  }

  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("El diagnóstico asociado debe tener al menos 10 caracteres.");
    return;
  }

  datos.diagnosticos.push({
    id: crearId("INC"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual
  });

  guardarDatos();
  document.querySelector("#formDiagnosticoTicket").reset();
  mostrarMensaje("RF38: diagnóstico asociado al ticket correctamente.");
}