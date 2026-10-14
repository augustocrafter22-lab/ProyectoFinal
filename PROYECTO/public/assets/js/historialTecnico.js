const selectEquipo = document.getElementById("historialTecnicoEquipoSelect");
const tablaHistorialTecnico = document.getElementById("tablaHistorialTecnico");

//GET - Obtiene los equipos para elegir de cuál ver el historial
async function obtenerEquipos() {
    const { datos } = await llamarApi("equipos");

    return datos;
}

//GET - Obtiene todos los eventos técnicos de un equipo
async function obtenerHistorial(idEquipo) {
    const { datos } = await llamarApi("historialEquipo", { parametros: { idEquipo: idEquipo } });

    return datos;
}

//Reemplaza la tabla por una sola fila con un aviso
function mostrarTablaVacia(texto) {
    const fila = document.createElement("tr");
    const celda = crearCelda(texto);
    celda.colSpan = 4;
    celda.className = "tablaVacia";
    fila.appendChild(celda);

    const cuerpo = document.createElement("tbody");
    cuerpo.appendChild(fila);
    tablaHistorialTecnico.replaceChildren(cuerpo);
}

function crearEncabezadoHistorial() {
    const filaEncabezado = document.createElement("tr");

    for (const clave of ["thTipo", "thDetalle", "thFecha", "thResponsable"]) {
        const columna = document.createElement("th");
        columna.textContent = traducir(`historialTecnico.${clave}`);
        filaEncabezado.appendChild(columna);
    }

    const encabezado = document.createElement("thead");
    encabezado.appendChild(filaEncabezado);

    return encabezado;
}

function mostrarHistorial(historial) {
    if (historial.length === 0) {
        mostrarTablaVacia(traducir("historialTecnico.sinEventos"));
        return;
    }

    const cuerpo = document.createElement("tbody");

    for (const evento of historial) {
        const fila = document.createElement("tr");
        const valores = [evento.tipo, evento.detalle, evento.fecha, evento.responsable];

        for (const valor of valores) {
            fila.appendChild(crearCelda(valor || "-"));
        }

        cuerpo.appendChild(fila);
    }

    tablaHistorialTecnico.replaceChildren(crearEncabezadoHistorial(), cuerpo);
}

//El equipo elegido viaja en la dirección de la página (?equipo=PC-01)
function guardarEquipoEnDireccion(idEquipo) {
    const url = new URL(window.location.href);

    if (idEquipo !== "") {
        url.searchParams.set("equipo", idEquipo);
    } else {
        url.searchParams.delete("equipo");
    }

    window.history.replaceState({}, "", url);
}

async function cargarHistorialTecnico() {
    const idEquipo = selectEquipo.value;

    guardarEquipoEnDireccion(idEquipo);

    if (idEquipo === "") {
        tablaHistorialTecnico.replaceChildren();
        return;
    }

    try {
        mostrarHistorial(await obtenerHistorial(idEquipo));
    } catch (error) {
        mostrarTablaVacia(error.message);
    }
}

async function cargarEquipos() {
    try {
        const equipos = await obtenerEquipos();

        agregarOpciones(selectEquipo, equipos, (equipo) => equipo.idEquipo, (equipo) => equipo.idEquipo);

        const idEquipoInicial = new URLSearchParams(window.location.search).get("equipo") || "";

        if (idEquipoInicial !== "") {
            selectEquipo.value = idEquipoInicial;
            await cargarHistorialTecnico();
        }
    } catch (error) {
        mostrarTablaVacia(error.message);
    }
}

selectEquipo.addEventListener("change", cargarHistorialTecnico);

async function iniciar() {
    await iniciarPagina();
    await cargarEquipos();
}

iniciar();
