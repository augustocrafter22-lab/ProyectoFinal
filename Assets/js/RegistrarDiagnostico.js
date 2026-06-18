document.addEventListener("DOMContentLoaded", iniciarRegistrarDiagnostico);

function iniciarRegistrarDiagnostico() {
  cargarSelectTickets("#registrarDiagnosticoTicket");

  const formulario = document.querySelector("#formregistrarDiagnostico");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarDiagnostico);
}

function registrarDiagnostico(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#registrarDiagnosticoTicket").value;
  const texto = document.querySelector("#registrarDiagnosticoDiagnostico").value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket.");
    return;
  }

  if (!Minimo(texto, 10)) {
    mostrarMensaje("El diagnóstico debe tener al menos 10 caracteres.");
    return;
  }

const diagnostico = obtenerDatos();

  const diagnostico = {
    id: crearId("INC"),
    ticketId: ticket.id,
    equipoId: ticket.equipoId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual
  };

  guardarDatos(diagnostico);

  document.querySelector("#formregistrarDiagnostico").reset();
  mostrarMensaje("Diagnóstico registrado correctamente.");
}

function obtenerDatos() {
    const datosGuardados = localStorage.getItem("diagnostico");
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

function guardarDatos(diagnostico) {
    localStorage.setItem("diagnostico", JSON.stringify(diagnostico));
}