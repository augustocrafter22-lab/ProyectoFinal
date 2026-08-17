document.addEventListener("DOMContentLoaded", iniciarConsultarDiagnostico);

function iniciarConsultarDiagnostico() {
  const params = new URLSearchParams(window.location.search);
  const ticketFiltro = params.get("ticket");

  if (ticketFiltro) {
    const titulo = document.querySelector("#consultarDiagnostico h2");
    if (titulo) {
      titulo.textContent = "Diagnósticos del ticket " + ticketFiltro;
    }
  }

  renderizarTabla(ticketFiltro);
}

function renderizarTabla(ticketFiltro) {
  const tabla = document.getElementById("tablaDiagnosticos");
  const todos = obtenerDatos();

  const diagnosticos = ticketFiltro
    ? todos.filter(function (d) { return d.ticketId === ticketFiltro; })
    : todos;

  if (diagnosticos.length === 0) {
    const cuerpo = document.createElement("tbody");
    const fila = document.createElement("tr");
    const celda = document.createElement("td");

    celda.textContent = ticketFiltro
      ? "No hay diagnósticos registrados para el ticket " + ticketFiltro + "."
      : "No hay diagnósticos registrados.";

    celda.setAttribute("colspan", "5");
    celda.className = "tabla-vacia";

    fila.appendChild(celda);
    cuerpo.appendChild(fila);
    tabla.replaceChildren(cuerpo);
    return;
  }

  const encabezado = document.createElement("thead");
  const filaEncabezado = document.createElement("tr");
  const columnas = ["ID", "Ticket", "Diagnóstico", "Fecha", "Técnico"];

  columnas.forEach(function (nombre) {
    const th = document.createElement("th");
    th.textContent = nombre;
    th.className = "tabla-th";
    filaEncabezado.appendChild(th);
  });

  encabezado.appendChild(filaEncabezado);


  const cuerpo = document.createElement("tbody");

  diagnosticos.forEach(function (diagnostico, indice) {
    const fila = document.createElement("tr");
    fila.className = indice % 2 === 0 ? "tabla-fila-par" : "tabla-fila-impar";

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
      td.className = "tabla-td";
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