document.addEventListener("DOMContentLoaded", iniciarRegistrarReemplazo);

function iniciarRegistrarReemplazo() {
  const formulario = document.getElementById("formRegistrarReemplazo");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", registrarReemplazo);
}

function registrarReemplazo(evento) {
  evento.preventDefault();

  const equipoId = document.getElementById("reemplazoEquipoSelect").value;
  const componente = document.getElementById("reemplazoComponente").value;
  const descripcion = document.getElementById("reemplazoDescripcion").value.trim();

  if (!equipoId) {
    mostrarMensaje("Seleccioná un equipo.");
    return;
  }

  if (!componente) {
    mostrarMensaje("Seleccioná el componente reemplazado.");
    return;
  }

  if (!validarMinimo(descripcion, 10)) {
    mostrarMensaje("La descripción debe tener al menos 10 caracteres.");
    return;
  }

  const reemplazos = obtenerDatos();

  const nuevoReemplazo = {
    id: crearId("RMP"),
    equipoId: equipoId,
    componente: componente,
    descripcion: descripcion,
    fecha: obtenerFechaActual(),
    tecnico: localStorage.getItem("CI")
  };

  reemplazos.push(nuevoReemplazo);
  guardarDatos(reemplazos);

  document.getElementById("formRegistrarReemplazo").reset();
  mostrarMensaje("Reemplazo de componente registrado correctamente.");
}

function obtenerDatos() {
  const datosGuardados = localStorage.getItem("historialReemplazos");
  return datosGuardados === null ? [] : JSON.parse(datosGuardados);
}

function guardarDatos(reemplazos) {
  localStorage.setItem("historialReemplazos", JSON.stringify(reemplazos));
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