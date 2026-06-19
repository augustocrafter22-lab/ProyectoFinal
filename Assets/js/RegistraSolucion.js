document.addEventListener("DOMContentLoaded", iniciarregistrarSolucion);

function iniciarregistrarSolucion() {
  const formulario = document.getElementById("formRegistrarSolucion");

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
  const ticketId = document.getElementById("registrarSolucionTicket").value;
  const texto = document.getElementById("registrarSolucionSolucion").value.trim();

  /* Validación de longitud mínima: evita soluciones vacías o triviales. */
  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("La solución debe tener al menos 10 caracteres.");
    return;
  }

  const soluciones = obtenerDatos();

  const nuevaSolucion = {
    id: crearId("SL"),
    ticketId: ticketId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual,
    tecnico: localStorage.getItem("CI")
  };

  soluciones.push(nuevaSolucion);
  guardarDatos(soluciones);

  document.getElementById("formRegistrarSolucion").reset();
  mostrarMensaje("Solución registrada correctamente.");
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
