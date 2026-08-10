document.addEventListener("DOMContentLoaded", iniciarRegistrarIntervencion);

function iniciarRegistrarIntervencion() {
  const formulario = document.getElementById("formRegistrarIntervencion");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarIntervencion);
}

function registrarIntervencion(evento) {
  evento.preventDefault();

  const equipoId = document.getElementById("intervencionEquipoSelect").value;
  const tipo = document.getElementById("intervencionTipo").value;
  const descripcion = document.getElementById("intervencionDescripcion").value.trim();

  if (!equipoId) {
    mostrarMensaje("Seleccioná un equipo.");
    return;
  }

  if (!tipo) {
    mostrarMensaje("Seleccioná el tipo de intervención.");
    return;
  }

  if (!validarMinimo(descripcion, 10)) {
    mostrarMensaje("La descripción debe tener al menos 10 caracteres.");
    return;
  }

  const intervenciones = obtenerDatos();

  const nuevaIntervencion = {
    id: crearId("INT"),
    equipoId: equipoId,
    tipo: tipo,
    descripcion: descripcion,
    fecha: obtenerFechaActual(),
    tecnico: localStorage.getItem("CI")
  };

  intervenciones.push(nuevaIntervencion);
  guardarDatos(intervenciones);

  document.getElementById("formRegistrarIntervencion").reset();
  mostrarMensaje("Intervención técnica registrada correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("historialIntervenciones");
  return datosGuardados === null ? [] : JSON.parse(datosGuardados);
}

function guardarDatos(intervenciones) {
  localStorage.setItem("historialIntervenciones", JSON.stringify(intervenciones));
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