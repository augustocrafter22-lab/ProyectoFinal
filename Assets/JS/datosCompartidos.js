/* ============================================================
   datosCompartidos.js — Capa de datos compartida
   ============================================================
   Proporciona funciones y estado global para toda la aplicación.
   Centraliza el acceso a localStorage y las operaciones CRUD
   para evitar duplicación de lógica entre módulos.
   ============================================================ */

/* Estado global de la aplicación.
   Se inicializa desde localStorage o con valores por defecto. */
const datos = JSON.parse(localStorage.getItem("datosApp")) || {
  equipos: [],
  tickets: [],
  diagnosticos: [],
  soluciones: [],
  usuarioActual: "Técnico",
};

/* Persiste el estado global en localStorage. */
function guardarDatos() {
  localStorage.setItem("datosApp", JSON.stringify(datos));
}

/* Genera un identificador único con el prefijo indicado.
   Ejemplo: crearId("EQ") → "EQ-0003" */
function crearId(prefijo) {
  const existentes = datos.equipos
    .concat(datos.tickets)
    .concat(datos.diagnosticos)
    .concat(datos.soluciones);
  const numeros = existentes
    .map(function (item) {
      return item.id;
    })
    .filter(function (id) {
      return id.startsWith(prefijo);
    })
    .map(function (id) {
      return parseInt(id.split("-")[1], 10);
    });
  const maximo = numeros.length > 0 ? Math.max.apply(null, numeros) : 0;
  const siguiente = String(maximo + 1).padStart(4, "0");
  return prefijo + "-" + siguiente;
}

/* Retorna la fecha actual formateada como dd/mm/aaaa. */
function obtenerFechaActual() {
  return new Date().toLocaleDateString("es-UY");
}

/* Valida que un texto tenga al menos la longitud mínima indicada. */
function validarMinimo(texto, minimo) {
  return texto.trim().length >= minimo;
}

/* Busca un ticket por su ID en el arreglo global. */
function obtenerTicket(id) {
  return datos.tickets.find(function (ticket) {
    return ticket.id === id;
  });
}

/* Puebla un elemento <select> con las opciones de tickets disponibles. */
function cargarSelectTickets(selector) {
  var select = document.querySelector(selector);
  if (!select) return;
  select.innerHTML = '<option value="">Seleccione un ticket</option>';
  datos.tickets.forEach(function (ticket) {
    var opcion = document.createElement("option");
    opcion.value = ticket.id;
    opcion.textContent = ticket.id + " - " + ticket.asunto;
    select.appendChild(opcion);
  });
}

/* Muestra un mensaje temporal al usuario en el elemento .mensaje-feedback.
   Requiere que exista un contenedor con esa clase en el HTML. */
function mostrarMensaje(texto) {
  var contenedor = document.querySelector(".mensaje-feedback");
  if (!contenedor) return;
  contenedor.textContent = texto;
  contenedor.className = "mensaje-feedback exito";
  /* Oculta el mensaje automáticamente después de 4 segundos. */
  setTimeout(function () {
    contenedor.className = "mensaje-feedback";
  }, 4000);
}
