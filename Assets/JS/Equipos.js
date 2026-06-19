/* ============================================================
   Equipos.js — Lógica de registro y listado de equipos
   ============================================================
   Dependencia: datosCompartidos.js (debe cargarse antes).
   ============================================================ */

/* Inicializa la página al cargar el DOM. */
document.addEventListener("DOMContentLoaded", iniciarPaginaEquipos);

function iniciarPaginaEquipos() {
  var formulario = document.getElementById("formEquipo");
  if (formulario) {
    formulario.addEventListener("submit", registrarEquipo);
  }
  renderizarTablaEquipos();
}

/* Construye una fila de tabla a partir de un objeto equipo. */
function crearFilaEquipo(equipo) {
  var fila = document.createElement("tr");
  fila.innerHTML =
    "<td>" +
    equipo.id +
    "</td>" +
    "<td>" +
    equipo.nombre +
    "</td>" +
    "<td>" +
    equipo.ubicacion +
    "</td>" +
    "<td>" +
    equipo.tipo +
    "</td>" +
    "<td>" +
    equipo.estado +
    "</td>" +
    "<td>" +
    equipo.fechaRegistro +
    "</td>";
  return fila;
}

/* Renderiza la tabla de equipos con los datos actuales.
   Refleja siempre el estado actual del arreglo global. */
function renderizarTablaEquipos() {
  var cuerpo = document.getElementById("cuerpoTablaEquipos");
  if (!cuerpo) return;
  cuerpo.innerHTML = "";
  datos.equipos.forEach(function (equipo) {
    cuerpo.appendChild(crearFilaEquipo(equipo));
  });
}

/* Maneja el envío del formulario: valida, crea el equipo y persiste. */
function registrarEquipo(evento) {
  evento.preventDefault();

  var nombre = document.getElementById("nombreEquipo").value.trim();
  var ubicacion = document.getElementById("laboratorioEquipo").value;
  var tipo = document.getElementById("tipoEquipo").value;
  var estado = document.getElementById("estadoEquipo").value;

  /* Previene registros con valores por defecto en selects. */
  if (!nombre || !ubicacion || !tipo || !estado) {
    mostrarMensaje("Complete todos los campos obligatorios.");
    return;
  }

  var equipo = {
    id: crearId("EQ"),
    nombre: nombre,
    ubicacion: ubicacion,
    tipo: tipo,
    estado: estado,
    fechaRegistro: obtenerFechaActual(),
  };

  datos.equipos.push(equipo);
  guardarDatos();
  renderizarTablaEquipos();
  document.getElementById("formEquipo").reset();
  mostrarMensaje("Equipo registrado correctamente.");
}
