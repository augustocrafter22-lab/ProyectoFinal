const mensajeVistaLab = document.getElementById("mensajeVistaLab");
const filtroPorFecha = document.getElementById("filtroPorFecha");
const filtroPorLaboratorio = document.getElementById("filtroPorLaboratorio");
const btnLimpiarFiltro = document.getElementById("btnLimpiarFiltro");
const cuerpoTablaSolicitudes = document.getElementById("cuerpoTablaSolicitudes");

//Solicitudes recibidas de la API; los filtros trabajan sobre esta lista
let solicitudes = [];

//GET - Obtiene todas las solicitudes de laboratorio
async function obtenerSolicitudes() {
    const { datos } = await llamarApi("solicitudes");

    return datos;
}

//GET - Obtiene los laboratorios para el filtro
async function obtenerLaboratorios() {
    const { datos } = await llamarApi("laboratorios");

    return datos;
}

function crearFilaSolicitud(solicitud) {
    const fila = document.createElement("tr");

    fila.append(
        crearCelda(solicitud.idSolicitud),
        crearCelda(solicitud.numeroLaboratorio),
        crearCelda(solicitud.solicitaSoftware ? traducir("vistaLab.si") : traducir("vistaLab.no")),
        crearCelda(solicitud.detalle ?? ""),
        crearCelda(solicitud.restricciones ?? ""),
        crearCelda(solicitud.fechaEstimada),
        crearCelda(solicitud.horaEstimada)
    );

    return fila;
}

//Muestra solo las solicitudes que coinciden con la fecha y el laboratorio elegidos
function mostrarSolicitudesFiltradas() {
    const fechaBuscada = filtroPorFecha.value;
    const laboratorioBuscado = filtroPorLaboratorio.value;

    const filtradas = solicitudes.filter((solicitud) => {
        const coincideFecha = fechaBuscada === "" || solicitud.fechaEstimada === fechaBuscada;
        const coincideLaboratorio = laboratorioBuscado === "" || solicitud.numeroLaboratorio === laboratorioBuscado;

        return coincideFecha && coincideLaboratorio;
    });

    cuerpoTablaSolicitudes.replaceChildren();

    if (filtradas.length === 0) {
        const fila = document.createElement("tr");
        const celda = crearCelda(traducir("vistaLab.sinSolicitudes"));
        celda.colSpan = 7;
        celda.className = "tablaVacia";
        fila.appendChild(celda);
        cuerpoTablaSolicitudes.appendChild(fila);
        return;
    }

    for (const solicitud of filtradas) {
        cuerpoTablaSolicitudes.appendChild(crearFilaSolicitud(solicitud));
    }
}

function limpiarFiltros() {
    filtroPorFecha.value = "";
    filtroPorLaboratorio.value = "";
    mostrarSolicitudesFiltradas();
}

async function cargarDatos() {
    try {
        const laboratorios = await obtenerLaboratorios();
        agregarOpciones(filtroPorLaboratorio, laboratorios, (lab) => lab.numeroLaboratorio, (lab) => lab.numeroLaboratorio);

        solicitudes = await obtenerSolicitudes();
        mostrarSolicitudesFiltradas();
    } catch (error) {
        mostrarMensaje(mensajeVistaLab, error.message, true);
    }
}

filtroPorFecha.addEventListener("change", mostrarSolicitudesFiltradas);
filtroPorLaboratorio.addEventListener("change", mostrarSolicitudesFiltradas);
btnLimpiarFiltro.addEventListener("click", limpiarFiltros);

async function iniciar() {
    await iniciarPagina();
    await cargarDatos();
}

iniciar();
