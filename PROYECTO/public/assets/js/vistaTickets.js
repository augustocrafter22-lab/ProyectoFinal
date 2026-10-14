const mensajeVistaTickets = document.getElementById("mensajeVistaTickets");
const listaTickets = document.getElementById("listaTickets");

const filtroEquipo = document.getElementById("filtroDeEquipos");
const filtroPrioridad = document.getElementById("filtroPrioridad");
const filtroEstado = document.getElementById("filtroEstado");
const buscadorDeTickets = document.getElementById("buscadorDeTickets");
const btnBuscarTicket = document.getElementById("btnBuscarTicket");
const fechaDesde = document.getElementById("fechaDesde");
const fechaHasta = document.getElementById("fechaHasta");
const btnFiltrarFechas = document.getElementById("btnFiltrarFechas");

//Los valores que se guardan en la base; el texto de cada opción se traduce según el idioma
const ESTADOS_TICKET = {
    "Pendiente": "vistaTickets.estadoPendiente",
    "En Proceso": "vistaTickets.estadoEnProceso",
    "Resuelto": "vistaTickets.estadoResuelto",
    "Cerrado": "vistaTickets.estadoCerrado"
};
const PRIORIDADES_TICKET = {
    "Indefinida": "vistaTickets.prioridadIndefinida",
    "Alta": "vistaTickets.prioridadAlta",
    "Media": "vistaTickets.prioridadMedia",
    "Baja": "vistaTickets.prioridadBaja"
};

/**
 * OPERACIONES CON LA API
 */

//GET - Obtiene todos los tickets
async function obtenerTickets() {
    const { datos } = await llamarApi("tickets");

    return datos;
}

//GET - Obtiene todos los equipos para el filtro
async function obtenerEquipos() {
    const { datos } = await llamarApi("equipos");

    return datos;
}

//PUT - Guarda el estado y la prioridad de un ticket
async function actualizarTicket(articulo) {
    const selectEstado = articulo.querySelector(".selectEstado");
    const selectPrioridad = articulo.querySelector(".selectPrioridad");

    try {
        await llamarApi("tickets", {
            metodo: "PUT",
            parametros: { id: articulo.dataset.id },
            cuerpo: { estado: selectEstado.value, prioridad: selectPrioridad.value }
        });

        //Lo que quedó guardado en la base es el valor nuevo
        selectEstado.dataset.valorGuardado = selectEstado.value;
        selectPrioridad.dataset.valorGuardado = selectPrioridad.value;
    } catch (error) {
        restaurarValoresGuardados([selectEstado, selectPrioridad]);
        mostrarMensaje(mensajeVistaTickets, error.message, true);
    }
}

//DELETE - Elimina un ticket
async function eliminarTicket(articulo) {
    const idTicket = articulo.dataset.id;

    if (!window.confirm(traducir("vistaTickets.confirmarEliminar", { ticket: idTicket }))) {
        return;
    }

    try {
        await llamarApi("tickets", { metodo: "DELETE", parametros: { id: idTicket } });

        articulo.remove();
    } catch (error) {
        mostrarMensaje(mensajeVistaTickets, error.message, true);
    }
}

/**
 * GESTIÓN DE LAS TARJETAS DE TICKETS
 */

//Deja los selects del ticket en el último valor guardado en la base
function restaurarValoresGuardados(selects) {
    for (const select of selects) {
        select.value = select.dataset.valorGuardado;
    }
}

function crearSelect(claseCss, textosPorValor, valorSeleccionado, prefijoTexto) {
    const select = document.createElement("select");
    select.className = claseCss;

    for (const valor in textosPorValor) {
        const opcion = document.createElement("option");
        opcion.value = valor;
        opcion.textContent = prefijoTexto + traducir(textosPorValor[valor]);
        opcion.selected = valor === valorSeleccionado;
        select.appendChild(opcion);
    }

    //Último valor guardado en la base, para volver a él si falla la actualización
    select.dataset.valorGuardado = select.value;

    return select;
}

function crearArticuloTicket(ticket) {
    const articulo = document.createElement("article");
    articulo.className = "ticket";
    articulo.dataset.id = ticket.idTicket;
    articulo.dataset.fecha = ticket.fechaCreacion;
    articulo.dataset.equipo = ticket.equipo;

    const seccionInfo = document.createElement("section");
    seccionInfo.className = "ticketInfo";

    const titulo = document.createElement("h3");
    const enlace = document.createElement("a");
    enlace.href = `consultarDiagnostico.html?ticket=${encodeURIComponent(ticket.idTicket)}`;
    enlace.textContent = ticket.idTicket;
    titulo.appendChild(enlace);

    const asunto = document.createElement("p");
    asunto.textContent = ticket.asunto;

    const equipo = document.createElement("p");
    equipo.textContent = ticket.equipo;

    seccionInfo.append(titulo, asunto, equipo);

    const seccionEstado = document.createElement("section");
    seccionEstado.className = "ticketEstado";

    const laboratorio = document.createElement("p");
    laboratorio.textContent = ticket.laboratorio;

    seccionEstado.append(
        crearSelect("selectEstado", ESTADOS_TICKET, ticket.estado, ""),
        crearSelect("selectPrioridad", PRIORIDADES_TICKET, ticket.prioridad, traducir("vistaTickets.prioridadPrefijo")),
        laboratorio
    );

    if (ticket.fechaFinalizacion) {
        const finalizado = document.createElement("p");
        finalizado.textContent = traducir("vistaTickets.finalizado", { fecha: ticket.fechaFinalizacion });
        seccionEstado.appendChild(finalizado);
    }

    const seccionAcciones = document.createElement("section");
    seccionAcciones.className = "ticketAcciones";
    seccionAcciones.appendChild(
        crearBoton(traducir("comun.btnEliminar"), "btnEliminarTicket", () => eliminarTicket(articulo))
    );

    articulo.append(seccionInfo, seccionEstado, seccionAcciones);

    return articulo;
}

async function cargarTickets() {
    try {
        const tickets = await obtenerTickets();

        listaTickets.replaceChildren();
        for (const ticket of tickets) {
            listaTickets.appendChild(crearArticuloTicket(ticket));
        }
    } catch (error) {
        mostrarMensaje(mensajeVistaTickets, error.message, true);
    }
}

async function cargarEquiposEnFiltro() {
    try {
        const equipos = await obtenerEquipos();

        agregarOpciones(filtroEquipo, equipos, (equipo) => equipo.idEquipo, (equipo) => equipo.idEquipo);
    } catch (error) {
        mostrarMensaje(mensajeVistaTickets, error.message, true);
    }
}

/**
 * FILTROS
 */

//Aplica todos los filtros a la vez (estado, prioridad, equipo, búsqueda por id y rango de fechas), así un filtro no pisa a los otros
function aplicarFiltros() {
    const textoBuscado = buscadorDeTickets.value.trim().toLowerCase();

    for (const articulo of listaTickets.querySelectorAll(".ticket")) {
        const coincideEstado = filtroEstado.value === "" || articulo.querySelector(".selectEstado").value === filtroEstado.value;
        const coincidePrioridad = filtroPrioridad.value === "" || articulo.querySelector(".selectPrioridad").value === filtroPrioridad.value;
        const coincideEquipo = filtroEquipo.value === "" || articulo.dataset.equipo === filtroEquipo.value;
        const coincideBusqueda = textoBuscado === "" || articulo.dataset.id.toLowerCase().includes(textoBuscado);

        //fechaCreacion llega como "AAAA-MM-DD HH:MM:SS", se toma solo el día. Se comparan los textos
        //"AAAA-MM-DD" en lugar de objetos Date, así no influye la zona horaria y el día "hasta" queda incluido.
        //Si una de las fechas está vacía, ese extremo del rango no se aplica.
        const diaTicket = articulo.dataset.fecha.split(" ")[0];
        const coincideFechas = (fechaDesde.value === "" || diaTicket >= fechaDesde.value) &&
            (fechaHasta.value === "" || diaTicket <= fechaHasta.value);

        const coincideTodo = coincideEstado && coincidePrioridad && coincideEquipo && coincideBusqueda && coincideFechas;
        articulo.style.display = coincideTodo ? "flex" : "none";
    }
}

/**
 * EVENTOS
 */

//Un solo oyente para los selects de todos los tickets
listaTickets.addEventListener("change", (evento) => {
    if (evento.target.matches(".selectEstado, .selectPrioridad")) {
        actualizarTicket(evento.target.closest(".ticket"));
    }
});

for (const filtro of [filtroPrioridad, filtroEstado, filtroEquipo]) {
    filtro.addEventListener("change", aplicarFiltros);
}
btnBuscarTicket.addEventListener("click", aplicarFiltros);
btnFiltrarFechas.addEventListener("click", aplicarFiltros);

async function iniciar() {
    await iniciarPagina();
    await cargarTickets();
    await cargarEquiposEnFiltro();
}

iniciar();
