const formularioEquipo = document.getElementById("formularioEquipo");
const cuerpoTablaEquipos = document.getElementById("cuerpoTablaEquipos");
const mensajeEquipos = document.getElementById("mensajeEquipos");
const leyendaFormularioEquipo = document.getElementById("leyendaFormularioEquipo");
const btnCancelarEdicionEquipo = document.getElementById("btnCancelarEdicionEquipo");

const campoIdEquipo = document.getElementById("idEquipo");
const campoIdLaboratorio = document.getElementById("idLaboratorio");
const campoMarca = document.getElementById("marca");
const campoEstado = document.getElementById("estado");
const campoDisponibilidad = document.getElementById("disponibilidad");
const campoInformacion = document.getElementById("informacion");

const filtroId = document.getElementById("filtroID");
const filtroLaboratorio = document.getElementById("filtroLab");
const filtroEstado = document.getElementById("filtroEstado");
const filtroDisponibilidad = document.getElementById("filtroDisponibilidad");

//Los valores que se guardan en la base están en español; esta tabla dice cómo se muestran en cada idioma
const TEXTOS_ESTADO = {
    "Dañado": "equipos.estadoDanado",
    "Funcionando": "equipos.estadoFuncionando",
    "En mantenimiento": "equipos.estadoMantenimiento",
    "No funciona": "equipos.estadoNoFunciona"
};
const TEXTOS_DISPONIBILIDAD = {
    "Disponible": "equipos.disponible",
    "No disponible": "equipos.noDisponible"
};

//Equipos recibidos de la API; los filtros trabajan sobre esta lista
let equipos = [];

//Auxiliar para saber si se está agregando o modificando un equipo
let equipoEnEdicion = false;

function traducirValor(textos, valor) {
    return textos[valor] ? traducir(textos[valor]) : valor;
}

/**
 * OPERACIONES CON LA API
 */

//GET - Obtiene todos los equipos
async function obtenerEquipos() {
    const { datos } = await llamarApi("equipos");

    return datos;
}

//GET - Obtiene un equipo específico mediante su id
async function obtenerEquipo(idEquipo) {
    const { datos } = await llamarApi("equipos", { parametros: { id: idEquipo } });

    return datos;
}

//GET - Obtiene los laboratorios para el formulario
async function obtenerLaboratorios() {
    const { datos } = await llamarApi("laboratorios");

    return datos;
}

/**
 * GESTIÓN DE FILAS DE LA TABLA
 */

function crearFilaEquipo(equipo) {
    const fila = document.createElement("tr");

    const cajaOperaciones = document.createElement("div");
    cajaOperaciones.className = "cajaOperaciones";
    cajaOperaciones.append(
        crearBoton(traducir("comun.btnModificar"), "btnAccion", () => abrirEdicionEquipo(equipo.idEquipo)),
        crearBoton(traducir("comun.btnEliminar"), "btnAccion btnPeligro", () => eliminarEquipo(equipo.idEquipo))
    );

    const celdaOperaciones = document.createElement("td");
    celdaOperaciones.appendChild(cajaOperaciones);

    fila.append(
        crearCelda(equipo.idEquipo),
        crearCelda(equipo.laboratorio),
        crearCelda(equipo.marca),
        crearCelda(traducirValor(TEXTOS_ESTADO, equipo.estado)),
        crearCelda(traducirValor(TEXTOS_DISPONIBILIDAD, equipo.disponibilidad)),
        crearCelda(equipo.informacion || ""),
        celdaOperaciones
    );

    return fila;
}

function contiene(texto, buscado) {
    return texto.toUpperCase().includes(buscado.trim().toUpperCase());
}

//Muestra solo los equipos que coinciden con todos los filtros escritos
function mostrarEquiposFiltrados() {
    const filtrados = equipos.filter((equipo) =>
        contiene(equipo.idEquipo, filtroId.value) &&
        contiene(equipo.laboratorio, filtroLaboratorio.value) &&
        contiene(traducirValor(TEXTOS_ESTADO, equipo.estado), filtroEstado.value) &&
        contiene(traducirValor(TEXTOS_DISPONIBILIDAD, equipo.disponibilidad), filtroDisponibilidad.value)
    );

    cuerpoTablaEquipos.replaceChildren();
    for (const equipo of filtrados) {
        cuerpoTablaEquipos.appendChild(crearFilaEquipo(equipo));
    }
}

async function cargarEquipos() {
    try {
        equipos = await obtenerEquipos();
        mostrarEquiposFiltrados();
    } catch (error) {
        mostrarMensaje(mensajeEquipos, error.message, true);
    }
}

async function cargarLaboratorios() {
    try {
        const laboratorios = await obtenerLaboratorios();

        agregarOpciones(campoIdLaboratorio, laboratorios, (lab) => lab.idLaboratorio, (lab) => lab.numeroLaboratorio);
    } catch (error) {
        mostrarMensaje(mensajeEquipos, error.message, true);
    }
}

/**
 * GESTIÓN DEL FORMULARIO
 */

function limpiarFormularioEquipo() {
    formularioEquipo.reset();
    equipoEnEdicion = false;
    leyendaFormularioEquipo.textContent = traducir("equipos.leyendaAlta");
    campoIdEquipo.readOnly = false;
    btnCancelarEdicionEquipo.hidden = true;

    const url = new URL(window.location.href);
    url.searchParams.delete("editar");
    window.history.replaceState({}, "", url);
}

async function abrirEdicionEquipo(idEquipo) {
    try {
        const equipo = await obtenerEquipo(idEquipo);

        equipoEnEdicion = true;
        leyendaFormularioEquipo.textContent = traducir("equipos.leyendaModificar");
        campoIdEquipo.value = equipo.idEquipo;
        campoIdEquipo.readOnly = true;
        campoIdLaboratorio.value = equipo.idLaboratorio;
        campoMarca.value = equipo.marca;
        campoEstado.value = equipo.estado;
        campoDisponibilidad.value = equipo.disponibilidad;
        campoInformacion.value = equipo.informacion || "";
        btnCancelarEdicionEquipo.hidden = false;

        const url = new URL(window.location.href);
        url.searchParams.set("editar", idEquipo);
        window.history.replaceState({}, "", url);
    } catch (error) {
        mostrarMensaje(mensajeEquipos, error.message, true);
    }
}

//DELETE - Elimina un equipo
async function eliminarEquipo(idEquipo) {
    try {
        const { mensaje } = await llamarApi("equipos", { metodo: "DELETE", parametros: { id: idEquipo } });

        mostrarMensaje(mensajeEquipos, mensaje, false);
        await cargarEquipos();
    } catch (error) {
        mostrarMensaje(mensajeEquipos, error.message, true);
    }
}

//Decide si debe realizarse un POST o un PUT dependiendo del estado del formulario
async function guardarEquipo(eventoFormulario) {
    eventoFormulario.preventDefault();

    const datosEquipo = {
        idEquipo: campoIdEquipo.value.trim(),
        idLaboratorio: campoIdLaboratorio.value,
        marca: campoMarca.value,
        estado: campoEstado.value,
        disponibilidad: campoDisponibilidad.value,
        informacion: campoInformacion.value.trim()
    };

    try {
        const { mensaje } = await llamarApi("equipos", {
            metodo: equipoEnEdicion ? "PUT" : "POST",
            parametros: equipoEnEdicion ? { id: datosEquipo.idEquipo } : {},
            cuerpo: datosEquipo
        });

        limpiarFormularioEquipo();
        mostrarMensaje(mensajeEquipos, mensaje, false);
        await cargarEquipos();
    } catch (error) {
        mostrarMensaje(mensajeEquipos, error.message, true);
    }
}

/**
 * EVENTOS
 */

formularioEquipo.addEventListener("submit", guardarEquipo);
btnCancelarEdicionEquipo.addEventListener("click", limpiarFormularioEquipo);

for (const filtro of [filtroId, filtroLaboratorio, filtroEstado, filtroDisponibilidad]) {
    filtro.addEventListener("input", mostrarEquiposFiltrados);
}

async function iniciar() {
    await iniciarPagina();
    await cargarLaboratorios();
    await cargarEquipos();

    //Una dirección con ?editar=PC-01 abre directamente la edición de ese equipo
    const idEquipoParaEditar = new URLSearchParams(window.location.search).get("editar");

    if (idEquipoParaEditar) {
        await abrirEdicionEquipo(idEquipoParaEditar);
    }
}

iniciar();
