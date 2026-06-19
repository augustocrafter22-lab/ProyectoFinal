document.addEventListener("DOMContentLoaded", iniciarregistrarSolucion);

function iniciarregistrarSolucion() {
  cargarSelectTickets("#registrarSolucionTicket");

  const formulario = document.querySelector("#formRegistrarSolucion");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarSolucion);
}

function registrarSolucion(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#registrarSolucionTicket").value;
  const texto = document
    .querySelector("#registrarSolucionSolucion")
    .value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket seleccionado.");
    return;
  }

  /* Validación de longitud mínima: evita soluciones vacías o triviales. */
  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("La solución debe tener al menos 10 caracteres.");
    return;
  }

  const solucion = {
    id: crearId("SL"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual,
  };

  datos.soluciones.push(solucion);
  guardarDatos();

  document.querySelector("#formRegistrarSolucion").reset();
  mostrarMensaje("Solución registrada correctamente.");
}
