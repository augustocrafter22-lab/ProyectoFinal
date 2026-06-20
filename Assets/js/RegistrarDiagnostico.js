document.addEventListener("DOMContentLoaded", iniciarRegistrarDiagnostico);

function iniciarRegistrarDiagnostico() {
  const formulario = document.getElementById("formregistrarDiagnostico");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarDiagnostico);
}

function registrarDiagnostico(evento) {
  evento.preventDefault();

  const ticketId = document.querySelector("#registrarDiagnosticoTicket").value;
  const texto = document
    .querySelector("#registrarDiagnosticoDiagnostico")
    .value.trim();
  const ticket = obtenerTicket(ticketId);

  if (!ticket) {
    mostrarMensaje("No se encontró el ticket.");
    return;
  }

  const ticketId = document.getElementById("registrarDiagnosticoTicket").value;
  const texto = document.getElementById("registrarDiagnosticoDiagnostico").value.trim();

  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("El diagnóstico debe tener al menos 10 caracteres.");
    return;
  }

  const diagnosticos = obtenerDatos();

  const nuevoDiagnostico = {
    id: crearId("INC"),
    ticketId: ticketId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: datos.usuarioActual,
    tecnico: localStorage.getItem("CI")
  };

  diagnosticos.push(nuevoDiagnostico);
  guardarDatos(diagnosticos);

  document.getElementById("formregistrarDiagnostico").reset();
  mostrarMensaje("Diagnóstico registrado correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("diagnosticos");

  if (datosGuardados === null) {
    return [];
  }

  return JSON.parse(datosGuardados);
}

function guardarDatos(diagnosticos) {
  localStorage.setItem("diagnosticos", JSON.stringify(diagnosticos));
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
