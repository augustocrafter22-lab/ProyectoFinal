document.addEventListener("DOMContentLoaded", iniciarAsociarSolucionTicket);

function iniciarAsociarSolucionTicket() {
  const formulario = document.getElementById("formAsociarSolucionTicket");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", asociarSolucionTicket);
}

function asociarSolucionTicket(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#AsociarSolucionTicketTicket").value;
  const texto = document
    .querySelector("#AsociarSolucionTicketSolucion")
    .value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket.");
    return;
  }
  const ticketId = document.getElementById("rf39Ticket").value;
  const texto = document.getElementById("AsociarSolucionTicketSolucion").value.trim();

  /* Validación de longitud mínima: evita soluciones vacías o triviales. */
  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("La solución asociada debe tener al menos 10 caracteres.");
    return;
  }

  const soluciones = obtenerDatos();

  const nuevaSolucion = {
    id: crearId("SL"),
    ticketId: ticketId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual,
  });
    tecnico: localStorage.getItem("CI")
  };

  soluciones.push(nuevaSolucion);
  guardarDatos(soluciones);

  document.getElementById("formAsociarSolucionTicket").reset();
  mostrarMensaje("La solución fue asociada al ticket correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("soluciones");

  if (datosGuardados === null) {
    return [];
  }

  return JSON.parse(datosGuardados);
}

function guardarDatos(soluciones) {
  localStorage.setItem("soluciones", JSON.stringify(soluciones));
}
function validarMinimo(texto, minimo) {
  return texto.length >= minimo;
}

function crearId(prefijo) {
  return prefijo + "-" + Date.now();
}

function obtenerFechaActual() {
  return new Date().toLocaleDateString("es-UY");
}

function mostrarMensaje(mensaje) {
  alert(mensaje);
}
