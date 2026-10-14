const formularioRegistrarDiagnostico = document.getElementById("formularioRegistrarDiagnostico");
const mensajeRegistrarDiagnostico = document.getElementById("mensajeRegistrarDiagnostico");
const selectTicket = document.getElementById("registrarDiagnosticoTicket");
const campoDiagnostico = document.getElementById("registrarDiagnosticoTexto");

//GET - Obtiene los tickets sobre los que se puede registrar un diagnóstico
async function obtenerTickets() {
    const { datos } = await llamarApi("tickets");

    return datos;
}

//POST - Registra el diagnóstico de un ticket
async function enviarDiagnostico(diagnostico) {
    return await llamarApi("diagnosticos", { metodo: "POST", cuerpo: diagnostico });
}

async function cargarTickets() {
    try {
        const tickets = await obtenerTickets();

        agregarOpciones(selectTicket, tickets, (ticket) => ticket.idTicket, (ticket) => `${ticket.idTicket} - ${ticket.asunto}`);
    } catch (error) {
        mostrarMensaje(mensajeRegistrarDiagnostico, error.message, true);
    }
}

async function registrarDiagnostico(eventoFormulario) {
    eventoFormulario.preventDefault();

    const diagnostico = {
        idTicket: selectTicket.value,
        cedulaTecnico: datosSesion.cedula,
        diagnostico: campoDiagnostico.value.trim()
    };

    try {
        const { mensaje } = await enviarDiagnostico(diagnostico);

        mostrarMensaje(mensajeRegistrarDiagnostico, mensaje, false);
        formularioRegistrarDiagnostico.reset();
    } catch (error) {
        mostrarMensaje(mensajeRegistrarDiagnostico, error.message, true);
    }
}

formularioRegistrarDiagnostico.addEventListener("submit", registrarDiagnostico);

async function iniciar() {
    await iniciarPagina();
    await cargarTickets();
}

iniciar();
