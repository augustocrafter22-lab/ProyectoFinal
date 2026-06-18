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

  const SolucionTicket = obtenerDatos();

  const SolucionTicket = {
    id: crearId("INC"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual
  };

  guardarDatos(SolucionTicket);

  document.querySelector("#formAsociarSolucionTicket").reset();
  mostrarMensaje("La solución fue asociada al ticket correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("SolucionTicket");
  if (datosGuardados === null) {
    return {
      id: [],
      ticketId: [],
      equipoId: [],
      texto: [],
      fecha: [],
      tecnico: null
    };
  }
  return JSON.parse(datosGuardados);
}

function guardarDatos(SolucionTicket) {
  const datos = localStorage.getItem("SolucionTicket", JSON.stringify(SolucionTicket));
}