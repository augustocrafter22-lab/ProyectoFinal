const formularioRegistrarReemplazo = document.getElementById("formularioRegistrarReemplazo");
const mensajeReemplazo = document.getElementById("mensajeReemplazo");
const selectEquipo = document.getElementById("reemplazoEquipoSelect");

//GET - Obtiene los equipos sobre los que se puede registrar un reemplazo
async function obtenerEquipos() {
    const { datos } = await llamarApi("equipos");

    return datos;
}

//POST - Registra el reemplazo de un componente de un equipo
async function enviarReemplazo(reemplazo) {
    return await llamarApi("reemplazos", { metodo: "POST", cuerpo: reemplazo });
}

async function cargarEquipos() {
    try {
        const equipos = await obtenerEquipos();

        agregarOpciones(selectEquipo, equipos, (equipo) => equipo.idEquipo, (equipo) => equipo.idEquipo);
    } catch (error) {
        mostrarMensaje(mensajeReemplazo, error.message, true);
    }
}

async function registrarReemplazo(eventoFormulario) {
    eventoFormulario.preventDefault();

    const reemplazo = {
        idEquipo: selectEquipo.value,
        componente: document.getElementById("reemplazoComponente").value.trim(),
        descripcion: document.getElementById("reemplazoDescripcion").value.trim()
    };

    try {
        const { mensaje } = await enviarReemplazo(reemplazo);

        mostrarMensaje(mensajeReemplazo, mensaje, false);
        formularioRegistrarReemplazo.reset();
    } catch (error) {
        mostrarMensaje(mensajeReemplazo, error.message, true);
    }
}

formularioRegistrarReemplazo.addEventListener("submit", registrarReemplazo);

async function iniciar() {
    await iniciarPagina();
    await cargarEquipos();
}

iniciar();
