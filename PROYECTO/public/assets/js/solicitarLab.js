const formularioSolicitud = document.getElementById("formularioSolicitud");
const mensajeSolicitud = document.getElementById("mensajeSolicitud");

const selectLaboratorio = document.getElementById("laboratorioSolicitud");
const selectSolicitaSoftware = document.getElementById("solicitaSoftware");
const campoDetalle = document.getElementById("detalleSoftware");
const campoRestricciones = document.getElementById("restricciones");
const campoFecha = document.getElementById("fechaEstimada");
const campoHora = document.getElementById("horaEstimada");

//GET - Obtiene los laboratorios que se pueden solicitar
async function obtenerLaboratorios() {
    const { datos } = await llamarApi("laboratorios");

    return datos;
}

//POST - Envía la solicitud de laboratorio del docente
async function enviarSolicitud(solicitud) {
    return await llamarApi("solicitudes", { metodo: "POST", cuerpo: solicitud });
}

async function cargarLaboratorios() {
    try {
        const laboratorios = await obtenerLaboratorios();

        agregarOpciones(selectLaboratorio, laboratorios, (lab) => lab.idLaboratorio, (lab) => lab.numeroLaboratorio);
    } catch (error) {
        mostrarMensaje(mensajeSolicitud, error.message, true);
    }
}

//No se puede pedir una fecha pasada, ni una hora que ya pasó si la fecha es hoy
function limitarFechaYHora() {
    const horaActual = new Date().toTimeString().slice(0, 5);

    campoFecha.min = obtenerFechaHoy();
    campoHora.min = campoFecha.value === obtenerFechaHoy() ? horaActual : "00:00";
}

//El detalle y las restricciones solo se completan cuando se solicita software
function actualizarCamposSoftware() {
    const solicitaSoftware = selectSolicitaSoftware.value === "Si";

    campoDetalle.required = solicitaSoftware;
    campoDetalle.disabled = !solicitaSoftware;
    campoRestricciones.required = solicitaSoftware;
    campoRestricciones.disabled = !solicitaSoftware;

    if (!solicitaSoftware) {
        campoDetalle.value = "";
        campoRestricciones.value = "";
    }
}

async function registrarSolicitud(eventoFormulario) {
    eventoFormulario.preventDefault();

    const solicitud = {
        idLaboratorio: selectLaboratorio.value,
        solicitaSoftware: selectSolicitaSoftware.value === "Si",
        detalle: campoDetalle.value.trim(),
        restricciones: campoRestricciones.value.trim(),
        fechaEstimada: campoFecha.value,
        horaEstimada: campoHora.value
    };

    try {
        const { mensaje } = await enviarSolicitud(solicitud);

        mostrarMensaje(mensajeSolicitud, mensaje, false);
        formularioSolicitud.reset();
        actualizarCamposSoftware();
        limitarFechaYHora();
    } catch (error) {
        mostrarMensaje(mensajeSolicitud, error.message, true);
    }
}

formularioSolicitud.addEventListener("submit", registrarSolicitud);
selectSolicitaSoftware.addEventListener("change", actualizarCamposSoftware);
campoFecha.addEventListener("change", limitarFechaYHora);

async function iniciar() {
    await iniciarPagina();
    actualizarCamposSoftware();
    limitarFechaYHora();
    await cargarLaboratorios();
}

iniciar();
