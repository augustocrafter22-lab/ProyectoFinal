const URL_API_TICKETS = "api/tickets.php";
const URL_API_EQUIPOS = "api/equipos.php";

const listaTickets = document.getElementById("listaTickets");
const filtroDeEquipos = document.getElementById("filtroDeEquipos");

const ESTADOS_TICKET = ["Pendiente", "En Proceso", "Resuelto", "Cerrado"];
const PRIORIDADES_TICKET = ["Indefinida", "Alta", "Media", "Baja"];

function crearSelect(clase, opciones, valorSeleccionado, prefijoTexto) {
  const select = document.createElement("select");
  select.classList.add(clase);

  for (const opcion of opciones) {
    const option = document.createElement("option");
    option.value = opcion;
    option.textContent = prefijoTexto ? `${prefijoTexto}${opcion}` : opcion;
    option.selected = opcion === valorSeleccionado;
    select.appendChild(option);
  }

  // Último valor guardado en la base, para volver a él si falla la actualización.
  select.dataset.valorGuardado = select.value;

  return select;
}

function crearArticuloTicket(ticket) {
  const articulo = document.createElement("article");
  articulo.classList.add("ticket");
  articulo.dataset.id = ticket.idTicket;
  articulo.dataset.fecha = ticket.fechaCreacion;
  articulo.dataset.equipo = ticket.equipo;

  const infoSeccion = document.createElement("section");
  infoSeccion.classList.add("ticketInfo");

  const titulo = document.createElement("h3");
  const enlace = document.createElement("a");
  enlace.href = `ConsultarDiagnostico.php?ticket=${encodeURIComponent(ticket.idTicket)}`;
  enlace.classList.add("ticket-enlace");
  enlace.textContent = ticket.idTicket;
  titulo.appendChild(enlace);

  const asunto = document.createElement("p");
  asunto.textContent = ticket.asunto;

  const equipo = document.createElement("p");
  equipo.textContent = ticket.equipo;

  infoSeccion.append(titulo, asunto, equipo);

  const estadoSeccion = document.createElement("section");
  estadoSeccion.classList.add("ticketEstado");

  const selectEstado = crearSelect("select-estado", ESTADOS_TICKET, ticket.estado);
  const selectPrioridad = crearSelect("select-prioridad", PRIORIDADES_TICKET, ticket.prioridad, "Prioridad: ");

  const laboratorio = document.createElement("p");
  laboratorio.classList.add("laboratorio");
  laboratorio.textContent = ticket.laboratorio;

  estadoSeccion.append(selectEstado, selectPrioridad, laboratorio);

  if (ticket.fechaFinalizacion) {
    const finalizado = document.createElement("p");
    finalizado.textContent = `Finalizado: ${ticket.fechaFinalizacion}`;
    estadoSeccion.appendChild(finalizado);
  }

  const accionesSeccion = document.createElement("section");
  accionesSeccion.classList.add("ticketAcciones");

  const btnEliminar = document.createElement("button");
  btnEliminar.type = "button";
  btnEliminar.classList.add("btn-eliminar-ticket");
  btnEliminar.textContent = "Eliminar";
  btnEliminar.addEventListener("click", () => eliminarTicket(articulo));

  accionesSeccion.appendChild(btnEliminar);

  articulo.append(infoSeccion, estadoSeccion, accionesSeccion);

  return articulo;
}

async function eliminarTicket(articulo) {
  const idTicket = articulo.dataset.id;

  if (!confirm(`¿Eliminar el ticket ${idTicket}?`)) {
    return;
  }

  try {
    const respuesta = await fetch(`${URL_API_TICKETS}?id=${encodeURIComponent(idTicket)}`, {
      method: "DELETE"
    });
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      alert(cuerpo.mensaje || "No se pudo eliminar el ticket.");
      return;
    }

    articulo.remove();
  } catch (error) {
    alert("Error de conexión al eliminar el ticket.");
  }
}

async function cargarTickets() {
  try {
    const respuesta = await fetch(URL_API_TICKETS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      alert(cuerpo.mensaje);
      return;
    }

    listaTickets.replaceChildren();
    for (const ticket of cuerpo.datos) {
      listaTickets.appendChild(crearArticuloTicket(ticket));
    }
  } catch (error) {
    alert("No se pudo conectar con el servidor.");
  }
}

async function cargarEquiposEnFiltro() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      return;
    }

    for (const equipo of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = equipo.idEquipo;
      option.textContent = equipo.idEquipo;
      filtroDeEquipos.appendChild(option);
    }
  } catch (error) {
    // Si no se pueden cargar los equipos, el filtro queda solo con "Todos los equipos".
  }
}

cargarTickets();
cargarEquiposEnFiltro();
