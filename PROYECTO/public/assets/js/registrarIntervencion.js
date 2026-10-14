const formularioRegistrarIntervencion = document.getElementById("formularioRegistrarIntervencion");
const mensajeIntervencion = document.getElementById("mensajeIntervencion");
const selectEquipo = document.getElementById("intervencionEquipoSelect");

//GET - Obtiene los equipos sobre los que se puede registrar una intervención
async function obtenerEquipos() {
    const { datos } = await llamarApi("equipos");

    return datos;
}

//POST - Registra la intervención técnica sobre un equipo
async function enviarIntervencion(intervencion) {
    return await llamarApi("intervenciones", { metodo: "POST", cuerpo: intervencion });
}

async function cargarEquipos() {
    try {
        const equipos = await obtenerEquipos();

        agregarOpciones(selectEquipo, equipos, (equipo) => equipo.idEquipo, (equipo) => equipo.idEquipo);
    } catch (error) {
        mostrarMensaje(mensajeIntervencion, error.message, true);
    }
}

async function registrarIntervencion(eventoFormulario) {
    eventoFormulario.preventDefault();

    const intervencion = {
        idEquipo: selectEquipo.value,
        tipo: document.getElementById("intervencionTipo").value.trim(),
        descripcion: document.getElementById("intervencionDescripcion").value.trim()
    };

    try {
        const { mensaje } = await enviarIntervencion(intervencion);

        mostrarMensaje(mensajeIntervencion, mensaje, false);
        formularioRegistrarIntervencion.reset();
    } catch (error) {
        mostrarMensaje(mensajeIntervencion, error.message, true);
    }
}

formularioRegistrarIntervencion.addEventListener("submit", registrarIntervencion);

async function iniciar() {
    await iniciarPagina();
    await cargarEquipos();
}

iniciar();
