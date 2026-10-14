const formularioRegistrarReparacion = document.getElementById("formularioRegistrarReparacion");
const mensajeRegistrarReparacion = document.getElementById("mensajeRegistrarReparacion");
const avisoSinDiagnosticos = document.getElementById("avisoSinDiagnosticos");
const selectDiagnostico = document.getElementById("registrarReparacionDiagnostico");
const campoReparacion = document.getElementById("registrarReparacionTexto");

//GET - Obtiene los diagnósticos sobre los que se puede registrar una reparación
async function obtenerDiagnosticos() {
    const { datos } = await llamarApi("diagnosticos");

    return datos;
}

//POST - Registra la reparación de un diagnóstico
async function enviarReparacion(reparacion) {
    return await llamarApi("reparaciones", { metodo: "POST", cuerpo: reparacion });
}

async function cargarDiagnosticos() {
    try {
        const diagnosticos = await obtenerDiagnosticos();

        if (diagnosticos.length === 0) {
            avisoSinDiagnosticos.hidden = false;
            formularioRegistrarReparacion.hidden = true;
            return;
        }

        agregarOpciones(
            selectDiagnostico,
            diagnosticos,
            (diagnostico) => diagnostico.idDiagnostico,
            (diagnostico) => `Ticket ${diagnostico.idTicket} - Equipo ${diagnostico.equipo} - ${diagnostico.diagnostico}`
        );
    } catch (error) {
        mostrarMensaje(mensajeRegistrarReparacion, error.message, true);
    }
}

async function registrarReparacion(eventoFormulario) {
    eventoFormulario.preventDefault();

    const reparacion = {
        idDiagnostico: selectDiagnostico.value,
        cedulaTecnico: datosSesion.cedula,
        reparacion: campoReparacion.value.trim()
    };

    try {
        const { mensaje } = await enviarReparacion(reparacion);

        mostrarMensaje(mensajeRegistrarReparacion, mensaje, false);
        formularioRegistrarReparacion.reset();
    } catch (error) {
        mostrarMensaje(mensajeRegistrarReparacion, error.message, true);
    }
}

formularioRegistrarReparacion.addEventListener("submit", registrarReparacion);

async function iniciar() {
    await iniciarPagina();
    await cargarDiagnosticos();
}

iniciar();
