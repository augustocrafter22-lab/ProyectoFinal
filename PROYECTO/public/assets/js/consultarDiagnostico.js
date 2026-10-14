const formularioFiltroDiagnosticos = document.getElementById("formularioFiltroDiagnosticos");
const campoFiltroTicket = document.getElementById("filtroTicket");
const btnQuitarFiltroTicket = document.getElementById("btnQuitarFiltroTicket");
const tituloConsultarDiagnosticos = document.getElementById("tituloConsultarDiagnosticos");
const encabezadoTablaDiagnosticos = document.getElementById("encabezadoTablaDiagnosticos");
const cuerpoTablaDiagnosticos = document.getElementById("cuerpoTablaDiagnosticos");

//GET - Obtiene los diagnósticos, opcionalmente solo los de un ticket
async function obtenerDiagnosticos(idTicket) {
    const { datos } = await llamarApi("diagnosticos", { parametros: { idTicket: idTicket } });

    return datos;
}

function crearFilaDiagnostico(diagnostico) {
    const fila = document.createElement("tr");

    fila.append(
        crearCelda(diagnostico.idDiagnostico),
        crearCelda(diagnostico.idTicket),
        crearCelda(diagnostico.diagnostico),
        crearCelda(diagnostico.fechaDiagnostico),
        crearCelda(diagnostico.cedulaTecnico)
    );

    return fila;
}

//Reemplaza la tabla por una sola fila con un aviso
function mostrarTablaVacia(texto) {
    encabezadoTablaDiagnosticos.hidden = true;
    cuerpoTablaDiagnosticos.replaceChildren();

    const fila = document.createElement("tr");
    const celda = crearCelda(texto);
    celda.colSpan = 5;
    celda.className = "tablaVacia";
    fila.appendChild(celda);
    cuerpoTablaDiagnosticos.appendChild(fila);
}

//El ticket a filtrar viaja en la dirección de la página (?ticket=INC-2026-0001)
function obtenerTicketFiltrado() {
    return new URLSearchParams(window.location.search).get("ticket") || "";
}

function guardarTicketFiltradoEnDireccion(idTicket) {
    const url = new URL(window.location.href);

    if (idTicket !== "") {
        url.searchParams.set("ticket", idTicket);
    } else {
        url.searchParams.delete("ticket");
    }

    window.history.replaceState({}, "", url);
}

async function cargarDiagnosticos() {
    const idTicket = obtenerTicketFiltrado();
    const hayFiltro = idTicket !== "";

    campoFiltroTicket.value = idTicket;
    btnQuitarFiltroTicket.hidden = !hayFiltro;
    tituloConsultarDiagnosticos.textContent = hayFiltro
        ? traducir("consultarDiagnostico.tituloFiltrado", { ticket: idTicket })
        : traducir("consultarDiagnostico.tituloTabla");

    try {
        const diagnosticos = await obtenerDiagnosticos(idTicket);

        if (diagnosticos.length === 0) {
            mostrarTablaVacia(
                hayFiltro
                    ? traducir("consultarDiagnostico.vacioFiltrado", { ticket: idTicket })
                    : traducir("consultarDiagnostico.vacio")
            );
            return;
        }

        encabezadoTablaDiagnosticos.hidden = false;
        cuerpoTablaDiagnosticos.replaceChildren();
        for (const diagnostico of diagnosticos) {
            cuerpoTablaDiagnosticos.appendChild(crearFilaDiagnostico(diagnostico));
        }
    } catch (error) {
        mostrarTablaVacia(error.message);
    }
}

formularioFiltroDiagnosticos.addEventListener("submit", (eventoFormulario) => {
    eventoFormulario.preventDefault();

    guardarTicketFiltradoEnDireccion(campoFiltroTicket.value.trim());
    cargarDiagnosticos();
});

btnQuitarFiltroTicket.addEventListener("click", () => {
    guardarTicketFiltradoEnDireccion("");
    cargarDiagnosticos();
});

async function iniciar() {
    await iniciarPagina();
    await cargarDiagnosticos();
}

iniciar();
