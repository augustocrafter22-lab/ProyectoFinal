const datos = JSON.parse(localStorage.getItem("datosApp")) || {
  equipos: [],
  tickets: [],
  diagnosticos: [],
  soluciones: [],
  usuarioActual: "Técnico",
};

function guardarDatos() {
  localStorage.setItem("datosApp", JSON.stringify(datos));
}

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

function obtenerFechaActual() {
  return new Date().toLocaleDateString("es-UY");
}

function validarMinimo(texto, minimo) {
  return texto.trim().length >= minimo;
}

function obtenerTicket(id) {
  return datos.tickets.find(function (ticket) {
    return ticket.id === id;
  });
}

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

function mostrarMensaje(texto) {
  var contenedor = document.querySelector(".mensaje-feedback");
  if (!contenedor) return;
  contenedor.textContent = texto;
  contenedor.className = "mensaje-feedback exito";

  setTimeout(function () {
    contenedor.className = "mensaje-feedback";
  }, 4000);
}
