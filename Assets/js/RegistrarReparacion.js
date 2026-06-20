document.addEventListener("DOMContentLoaded", iniciarRegistrarReparacion);

function iniciarRegistrarReparacion() {
  const formulario = document.getElementById("formRegistrarReparacion");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarReparacion);
}

function registrarReparacion(evento) {
  evento.preventDefault();

  const equipoId = document.getElementById("reparacionEquipoSelect").value;
  const descripcion = document.getElementById("reparacionDescripcion").value.trim();

  if (!equipoId) {
    mostrarMensaje("Seleccioná un equipo.");
    return;
  }

  if (!validarMinimo(descripcion, 10)) {
    mostrarMensaje("La descripción debe tener al menos 10 caracteres.");
    return;
  }

  const reparaciones = obtenerDatos();

  const nuevaReparacion = {
    id: crearId("REP"),
    equipoId: equipoId,
    descripcion: descripcion,
    fecha: obtenerFechaActual(),
    tecnico: localStorage.getItem("CI")
  };

  reparaciones.push(nuevaReparacion);
  guardarDatos(reparaciones);

  document.getElementById("formRegistrarReparacion").reset();
  mostrarMensaje("Reparación registrada correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("historialReparaciones");
  return datosGuardados === null ? [] : JSON.parse(datosGuardados);
}

function guardarDatos(reparaciones) {
  localStorage.setItem("historialReparaciones", JSON.stringify(reparaciones));
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