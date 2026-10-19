const formularioRegistrarSolucion = document.getElementById("formularioRegistrarSolucion");
const mensajeRegistrarSolucion = document.getElementById("mensajeRegistrarSolucion");
const avisoSinDiagnosticos = document.getElementById("avisoSinDiagnosticos");
const selectDiagnostico = document.getElementById("registrarSolucionDiagnostico");
const campoSolucion = document.getElementById("registrarSolucionTexto");

//GET - Obtiene los diagnósticos sobre los que se puede registrar una solución
async function obtenerDiagnosticos() {
    const { datos } = await llamarApi("diagnosticos");

    return datos;
}

//POST - Registra la solución de un diagnóstico
async function enviarSolucion(solucion) {
    return await llamarApi("soluciones", { metodo: "POST", cuerpo: solucion });
}

async function cargarDiagnosticos() {
    try {
        const diagnosticos = await obtenerDiagnosticos();

        if (diagnosticos.length === 0) {
            avisoSinDiagnosticos.hidden = false;
            formularioRegistrarSolucion.hidden = true;
            return;
        }

        agregarOpciones(
            selectDiagnostico,
            diagnosticos,
            (diagnostico) => diagnostico.idDiagnostico,
            (diagnostico) => traducir("registrarSolucion.opcionDiagnostico", {
                ticket: diagnostico.idTicket,
                equipo: diagnostico.equipo,
                diagnostico: diagnostico.diagnostico
            })
        );
    } catch (error) {
        mostrarMensaje(mensajeRegistrarSolucion, error.message, true);
    }
}

async function registrarSolucion(eventoFormulario) {
    eventoFormulario.preventDefault();

    const solucion = {
        idDiagnostico: selectDiagnostico.value,
        solucion: campoSolucion.value.trim()
    };

    try {
        const { mensaje } = await enviarSolucion(solucion);

        mostrarMensaje(mensajeRegistrarSolucion, mensaje, false);
        formularioRegistrarSolucion.reset();
    } catch (error) {
        mostrarMensaje(mensajeRegistrarSolucion, error.message, true);
    }
}

formularioRegistrarSolucion.addEventListener("submit", registrarSolucion);

async function iniciar() {
    await iniciarPagina();
    await cargarDiagnosticos();
}

iniciar();
