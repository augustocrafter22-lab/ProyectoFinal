const enlaceRegresar = document.getElementById("enlaceRegresar");

const cantReportes = document.getElementById("cantReportes");
const cantSinAtender = document.getElementById("cantSinAtender");
const cantEnProceso = document.getElementById("cantEnProceso");
const cantResueltas = document.getElementById("cantResueltas");

const contadorPendiente = document.getElementById("contadorPendiente");
const contadorEnProceso = document.getElementById("contadorEnProceso");
const contadorResuelto = document.getElementById("contadorResuelto");
const contadorCerrado = document.getElementById("contadorCerrado");
const contadorTotal = document.getElementById("contadorTotal");

const cuerpoTiempos = document.getElementById("cuerpoTiempos");
const cuerpoSalones = document.getElementById("cuerpoSalones");

//GET - Obtiene las métricas de los tickets
async function obtenerMetricas() {
    const { datos } = await llamarApi("dashboard");

    return datos;
}

//El coordinador vuelve a su panel y el técnico al suyo
function configurarEnlaceRegresar() {
    const esCoordinador = datosSesion.roles.includes("coordinador");

    enlaceRegresar.href = esCoordinador ? "administrador.html" : "tecnico.html";
}

function mostrarConteos(metricas) {
    const porEstado = metricas.porEstado;

    cantReportes.textContent = metricas.totalReportes;
    cantSinAtender.textContent = porEstado["Pendiente"];
    cantEnProceso.textContent = porEstado["En Proceso"];
    cantResueltas.textContent = porEstado["Resuelto"];

    contadorPendiente.textContent = porEstado["Pendiente"];
    contadorEnProceso.textContent = porEstado["En Proceso"];
    contadorResuelto.textContent = porEstado["Resuelto"];
    contadorCerrado.textContent = porEstado["Cerrado"];
    contadorTotal.textContent = metricas.totalReportes;
}

//Llena una tabla de dos columnas; si no hay filas muestra el texto indicado
function llenarTablaDosColumnas(cuerpoTabla, filas, claveTextoVacio) {
    cuerpoTabla.replaceChildren();

    if (filas.length === 0) {
        const fila = document.createElement("tr");
        const celda = crearCelda(traducir(claveTextoVacio));
        celda.colSpan = 2;
        fila.appendChild(celda);
        cuerpoTabla.appendChild(fila);
        return;
    }

    for (const [primeraColumna, segundaColumna] of filas) {
        const fila = document.createElement("tr");
        fila.append(crearCelda(primeraColumna), crearCelda(segundaColumna));
        cuerpoTabla.appendChild(fila);
    }
}

async function cargarDashboard() {
    try {
        const metricas = await obtenerMetricas();

        mostrarConteos(metricas);
        llenarTablaDosColumnas(
            cuerpoTiempos,
            metricas.tiemposResolucion.map((tiempo) => [tiempo.idTicket, tiempo.dias]),
            "dashboard.sinTicketsResueltos"
        );
        llenarTablaDosColumnas(
            cuerpoSalones,
            metricas.incidenciasPorSalon.map((salon) => [salon.laboratorio, salon.cantidad]),
            "dashboard.sinIncidencias"
        );
    } catch (error) {
        window.alert(error.message);
    }
}

async function iniciar() {
    await iniciarPagina();
    configurarEnlaceRegresar();
    await cargarDashboard();
}

iniciar();
