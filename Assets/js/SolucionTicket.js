document.addEventListener("DOMContentLoaded", iniciarAsociarSolucionTicket);

function iniciarAsociarSolucionTicket() {
  cargarSelectTickets("#AsociarSolucionTicketTicket");

  const formulario = document.querySelector("#formAsociarSolucionTicket");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", asociarSolucionTicket);
}

function asociarSolucionTicket(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#AsociarSolucionTicketTicket").value;
  const texto = document.querySelector("#AsociarSolucionTicketSolucion").value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket.");
    return;
  }

  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("La solución asociada debe tener al menos 10 caracteres.");
    return;
  }

  datos.soluciones.push({
    id: crearId("INC"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual
  });

  guardarDatos();
  document.querySelector("#formAsociarSolucionTicket").reset();
  mostrarMensaje("La solución fue asociada al ticket correctamente.");
}