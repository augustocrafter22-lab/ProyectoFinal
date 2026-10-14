const formularioTicket = document.getElementById("formularioTicket");
const mensajeIngresoTickets = document.getElementById("mensajeIngresoTickets");

const selectLaboratorio = document.getElementById("laboratorio");
const selectEquipo = document.getElementById("equipo");

//GET - Obtiene los laboratorios para elegir dónde ocurrió la incidencia
async function obtenerLaboratorios() {
    const { datos } = await llamarApi("laboratorios");

    return datos;
}

//GET - Obtiene los equipos del laboratorio elegido
async function obtenerEquiposDelLaboratorio(idLaboratorio) {
    const { datos } = await llamarApi("equipos", { parametros: { laboratorio: idLaboratorio } });

    return datos;
}

//POST - Envía el ticket nuevo
async function enviarTicket(ticket) {
    return await llamarApi("tickets", { metodo: "POST", cuerpo: ticket });
}

async function cargarLaboratorios() {
    try {
        const laboratorios = await obtenerLaboratorios();

        agregarOpciones(selectLaboratorio, laboratorios, (lab) => lab.idLaboratorio, (lab) => lab.numeroLaboratorio);
    } catch (error) {
        mostrarMensaje(mensajeIngresoTickets, error.message, true);
    }
}

//Cada vez que cambia el laboratorio, la lista de equipos muestra solo los de ese laboratorio
async function cargarEquipos() {
    vaciarOpciones(selectEquipo);

    if (selectLaboratorio.value === "") {
        return;
    }

    try {
        const equipos = await obtenerEquiposDelLaboratorio(selectLaboratorio.value);

        agregarOpciones(selectEquipo, equipos, (equipo) => equipo.idEquipo, (equipo) => `${equipo.idEquipo} - ${equipo.marca}`);
    } catch (error) {
        mostrarMensaje(mensajeIngresoTickets, error.message, true);
    }
}

async function registrarTicket(eventoFormulario) {
    eventoFormulario.preventDefault();

    const ticket = {
        laboratorio: selectLaboratorio.value,
        equipo: selectEquipo.value,
        asunto: document.getElementById("asunto").value.trim(),
        descripcion: document.getElementById("descripcion").value.trim(),
        turno: document.getElementById("turno").value,
        grupo: document.getElementById("grupo").value.trim(),
        profesor: document.getElementById("profesor").value.trim()
    };

    try {
        const { mensaje } = await enviarTicket(ticket);

        mostrarMensaje(mensajeIngresoTickets, mensaje, false);
        formularioTicket.reset();
        await cargarEquipos();
    } catch (error) {
        mostrarMensaje(mensajeIngresoTickets, error.message, true);
    }
}

formularioTicket.addEventListener("submit", registrarTicket);
selectLaboratorio.addEventListener("change", cargarEquipos);

async function iniciar() {
    await iniciarPagina();
    await cargarLaboratorios();
}

iniciar();
