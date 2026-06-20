const cuerpoTabla = document.getElementById("cuerpoTabla");
const filtroPorFecha = document.getElementById("filtroPorFecha");
const filtroPorLaboratorio = document.getElementById("filtroPorLaboratorio");
const btnEliminar = document.getElementById("btnEliminar");
const btnLimpiarFiltro = document.getElementById("btnLimpiarFiltro");
const seleccionarTodos = document.getElementById("seleccionarTodos");

let solicitudes = JSON.parse(localStorage.getItem('Solicitudes')) || [];

function crearFila(solicitud) {
    const fila = document.createElement("tr");
    fila.dataset.id = solicitud.id;

    const valores = [
        solicitud.id,
        solicitud.laboratorio,
        solicitud.solicitudSoftware,
        solicitud.detalleSoftware,
        solicitud.Restricciones,
        solicitud.fechaEstimada,
        solicitud.horaEstimada,
    ];

    valores.forEach(function (valor) {
        const celda = document.createElement("td");
        celda.textContent = valor;
        fila.appendChild(celda);
    });

    return fila;
}

function renderizarTabla(datos) {
    while (cuerpoTabla.firstChild) {
        cuerpoTabla.removeChild(cuerpoTabla.firstChild);
    }

    if (datos.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.textContent = "No hay solicitudes que coincidan.";
        celda.colSpan = 7;
        fila.appendChild(celda);
        cuerpoTabla.appendChild(fila);
        return;
    }

    datos.forEach(function (solicitud) {
        cuerpoTabla.appendChild(crearFila(solicitud));
    });
}
renderizarTabla(solicitudes);
