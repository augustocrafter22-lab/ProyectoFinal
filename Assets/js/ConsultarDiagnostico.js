document.addEventListener("DOMContentLoaded", iniciarConsultarDiagnostico);

function iniciarConsultarDiagnostico() {
    renderizarTabla();
}

function renderizarTabla() {
    const tabla = document.getElementById("tablaDiagnosticos");
    const diagnosticos = obtenerDatos();

    if (diagnosticos.length === 0) {
    const fila = document.createElement("tr");
    const celda = document.createElement("td");
    celda.textContent = "No hay diagnósticos registrados.";
    celda.setAttribute("colspan", "5");
    celda.style.textAlign = "center";
    celda.style.padding = "16px";
    celda.style.color = "#6b7280";
    fila.appendChild(celda);

    const cuerpo = document.createElement("tbody");
    cuerpo.appendChild(fila);
    tabla.replaceChildren(cuerpo);
    return;
    }

  // Encabezado
    const encabezado = document.createElement("thead");
    const filaEncabezado = document.createElement("tr");
    const columnas = ["ID", "Ticket", "Diagnóstico", "Fecha", "Técnico"];

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

  // Cuerpo
    const cuerpo = document.createElement("tbody");

    diagnosticos.forEach(function (diagnostico, indice) {
    const fila = document.createElement("tr");
    fila.style.backgroundColor = indice % 2 === 0 ? "#f6f7fc" : "#ffffff";

    const valores = [
    diagnostico.id,
    diagnostico.ticketId,
    diagnostico.texto,
    diagnostico.fecha,
    diagnostico.tecnico
    ];

    valores.forEach(function (valor) {
    const td = document.createElement("td");
    td.textContent = valor || "-";
    td.style.padding = "8px 10px";
    td.style.borderBottom = "1px solid #d7dbe8";
    td.style.fontSize = "13px";
    fila.appendChild(td);
    });

    cuerpo.appendChild(fila);
    });

    tabla.replaceChildren(encabezado, cuerpo);
}

function obtenerDatos() {
    const datosGuardados = localStorage.getItem("diagnosticos");

    if (datosGuardados === null) {
    return [];
    }

    return JSON.parse(datosGuardados);
}