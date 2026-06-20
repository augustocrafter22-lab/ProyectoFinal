document.addEventListener("DOMContentLoaded", iniciarHistorialEstados);

function iniciarHistorialEstados() {
        const selectEquipo = document.getElementById("historialEstadosEquipoSelect");
        selectEquipo.addEventListener("change", renderizarHistorialEstados);
}

function renderizarHistorialEstados() {
        const equipoId = document.getElementById("historialEstadosEquipoSelect").value;
        const tabla = document.getElementById("tablaHistorialEstados");

    if (!equipoId) {
        tabla.replaceChildren();
        return;
}

const historial = obtenerHistorialEstados()
        .filter(function (r) { return r.equipoId === equipoId; });

    if (historial.length === 0) {
        const cuerpo = document.createElement("tbody");
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.textContent = "No hay historial de estados para este equipo.";
        celda.setAttribute("colspan", "5");
        celda.style.textAlign = "center";
        celda.style.padding = "16px";
        celda.style.color = "#6b7280";
        fila.appendChild(celda);
        cuerpo.appendChild(fila);
        tabla.replaceChildren(cuerpo);
        return;
}

    const encabezado = document.createElement("thead");
    const filaEncabezado = document.createElement("tr");
    const columnas = ["Estado anterior", "Estado nuevo", "Fecha inicio", "Fecha fin", "Usuario"];

columnas.forEach(function (nombre) {
        const th = document.createElement("th");
        th.textContent = nombre;
        th.style.textAlign = "left";
        th.style.padding = "8px 10px";
        th.style.borderBottom = "2px solid #1956c0";
        th.style.color = "#153894";
        th.style.fontSize = "13px";
        filaEncabezado.appendChild(th);
});

encabezado.appendChild(filaEncabezado);


    const cuerpo = document.createElement("tbody");

    historial.forEach(function (registro, indice) {
        const fila = document.createElement("tr");
        fila.style.backgroundColor = indice % 2 === 0 ? "#f6f7fc" : "#ffffff";

        const siguienteRegistro = historial[indice + 1];
        const fechaFin = siguienteRegistro ? siguienteRegistro.fecha : "Estado actual";

    const valores = [
        registro.estadoAnterior,
        registro.estadoNuevo,
        registro.fecha,
        fechaFin,
        registro.usuario || "-"
    ];

    valores.forEach(function (valor, columnaIndice) {
        const td = document.createElement("td");
        td.textContent = valor || "-";
        td.style.padding = "8px 10px";
        td.style.borderBottom = "1px solid #d7dbe8";
        td.style.fontSize = "13px";


    if (columnaIndice === 3 && valor === "Estado actual") {
        td.style.color = "#1956c0";
        td.style.fontWeight = "600";
}

    fila.appendChild(td);
});

    cuerpo.appendChild(fila);
});

tabla.replaceChildren(encabezado, cuerpo);
}

function obtenerHistorialEstados() {
        const datos = localStorage.getItem("historialEstados");
        return datos === null ? [] : JSON.parse(datos);
}