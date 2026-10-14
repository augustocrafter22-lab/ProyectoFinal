const formularioModificarDiagnostico = document.getElementById("formularioModificarDiagnostico");
const mensajeModificarDiagnostico = document.getElementById("mensajeModificarDiagnostico");
const selectDiagnostico = document.getElementById("modificarDiagnosticoSelect");
const campoTextoDiagnostico = document.getElementById("modificarDiagnosticoTexto");

//GET - Obtiene los diagnósticos registrados
async function obtenerDiagnosticos() {
    const { datos } = await llamarApi("diagnosticos");

    return datos;
}

//PUT - Guarda el nuevo texto de un diagnóstico
async function enviarModificacion(idDiagnostico, texto) {
    return await llamarApi("diagnosticos", {
        metodo: "PUT",
        parametros: { id: idDiagnostico },
        cuerpo: { diagnostico: texto }
    });
}

async function cargarDiagnosticos() {
    try {
        const diagnosticos = await obtenerDiagnosticos();

        agregarOpciones(
            selectDiagnostico,
            diagnosticos,
            (diagnostico) => diagnostico.idDiagnostico,
            (diagnostico) => `${diagnostico.idDiagnostico} | ${diagnostico.idTicket} | ${diagnostico.fechaDiagnostico}`
        );

        //El texto de cada diagnóstico se guarda en su opción para mostrarlo al elegirla
        diagnosticos.forEach((diagnostico, indice) => {
            selectDiagnostico.options[indice + 1].dataset.texto = diagnostico.diagnostico;
        });
    } catch (error) {
        mostrarMensaje(mensajeModificarDiagnostico, error.message, true);
    }
}

function completarTextoDiagnostico() {
    const opcionSeleccionada = selectDiagnostico.options[selectDiagnostico.selectedIndex];

    campoTextoDiagnostico.value = opcionSeleccionada.dataset.texto || "";
}

async function modificarDiagnostico(eventoFormulario) {
    eventoFormulario.preventDefault();

    try {
        const { mensaje } = await enviarModificacion(selectDiagnostico.value, campoTextoDiagnostico.value.trim());

        mostrarMensaje(mensajeModificarDiagnostico, mensaje, false);
        selectDiagnostico.options[selectDiagnostico.selectedIndex].dataset.texto = campoTextoDiagnostico.value.trim();
    } catch (error) {
        mostrarMensaje(mensajeModificarDiagnostico, error.message, true);
    }
}

selectDiagnostico.addEventListener("change", completarTextoDiagnostico);
formularioModificarDiagnostico.addEventListener("submit", modificarDiagnostico);

async function iniciar() {
    await iniciarPagina();
    await cargarDiagnosticos();
}

iniciar();
