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
  const texto = document.querySelector("#registrarSolucionSolucion").value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket seleccionado.");
    return;
  }

  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("La solución debe tener al menos 10 caracteres.");
    return;
  }

const solucion = obtenerDatos();

  const solucion = {
    id: crearId("SL"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual
  };

  guardarDatos(solucion);

  document.querySelector("#formRegistrarSolucion").reset();
  mostrarMensaje("Solución registrada correctamente.");
}

function obtenerDatos() {
    const datosGuardados = localStorage.getItem("solucion");
    if (datosGuardados === null) {
      return {
      id: [],
      ticketId: [],
      equipoId: [],
      texto: [],
      fecha: [],
      tecnico: null
      }
    }
    return JSON.parse(datosGuardados);
  }

  function guardarDatos(solucion) {
    localStorage.setItem("solucion", JSON.stringify(solucion));
  }