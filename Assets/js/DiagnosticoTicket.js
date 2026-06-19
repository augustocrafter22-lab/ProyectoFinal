document.addEventListener("DOMContentLoaded", iniciarDiagnosticoTicket);

function iniciarDiagnosticoTicket() {
  const formulario = document.getElementById("formDiagnosticoTicket");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", asociarDiagnosticoTicket);
}

function asociarDiagnosticoTicket(evento) {
  evento.preventDefault();

  const ticketId = document.getElementById("DiagnosticoTicketTicket").value;
  const texto = document.getElementById("DiagnosticoTicketDiagnostico").value.trim();

  if (!validarMinimo(texto, 10)) {
    mostrarMensaje("El diagnóstico asociado debe tener al menos 10 caracteres.");
    return;
  }

  const diagnosticos = obtenerDatos();

  const nuevoDiagnostico = {
    id: crearId("INC"),
    ticketId: ticketId,
    texto: texto,
    fecha: obtenerFechaActual(),
    tecnico: localStorage.getItem("CI")
  };

  diagnosticos.push(nuevoDiagnostico);
  guardarDatos(diagnosticos);

  document.getElementById("formDiagnosticoTicket").reset();
  mostrarMensaje("Diagnóstico asociado al ticket correctamente.");
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