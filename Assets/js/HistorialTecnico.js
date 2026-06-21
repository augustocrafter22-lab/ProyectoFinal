document.addEventListener("DOMContentLoaded", iniciarHistorialTecnico);

function iniciarHistorialTecnico() {
  const selectEquipo = document.getElementById("historialTecnicoEquipoSelect");
  selectEquipo.addEventListener("change", renderizarHistorialTecnico);
}

function renderizarHistorialTecnico() {
  const equipoId = document.getElementById("historialTecnicoEquipoSelect").value;
  const tabla = document.getElementById("tablaHistorialTecnico");

  if (!equipoId) {
    tabla.replaceChildren();
    return;
  }

  const reparaciones = obtenerReparaciones()
    .filter(function (r) { return r.equipoId === equipoId; })
    .map(function (r) {
      return {
        tipo: "Reparación",
        detalle: r.descripcion,
        fecha: r.fecha,
        tecnico: r.tecnico
      };
    });

  const intervenciones = obtenerIntervenciones()
    .filter(function (r) { return r.equipoId === equipoId; })
    .map(function (r) {
      return {
        tipo: "Intervención (" + r.tipo + ")",
        detalle: r.descripcion,
        fecha: r.fecha,
        tecnico: r.tecnico
      };
    });

  const reemplazos = obtenerReemplazos()
    .filter(function (r) { return r.equipoId === equipoId; })
    .map(function (r) {
      return {
        tipo: "Reemplazo (" + r.componente + ")",
        detalle: r.descripcion,
        fecha: r.fecha,
        tecnico: r.tecnico
      };
    });

  const registros = reparaciones.concat(intervenciones).concat(reemplazos);

  registros.sort(function (a, b) {
    return new Date(b.fecha) - new Date(a.fecha);
  });

  if (registros.length === 0) {
    const cuerpo = document.createElement("tbody");
    const fila = document.createElement("tr");
    const celda = document.createElement("td");
    celda.textContent = "No hay registros técnicos para este equipo.";
    celda.setAttribute("colspan", "4");
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
  const columnas = ["Tipo", "Descripción", "Fecha", "Técnico"];

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

  registros.forEach(function (registro, indice) {
    const fila = document.createElement("tr");
    fila.style.backgroundColor = indice % 2 === 0 ? "#f6f7fc" : "#ffffff";

    const valores = [
      registro.tipo,
      registro.detalle,
      registro.fecha,
      registro.tecnico || "-"
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

function obtenerReparaciones() {
  const datos = localStorage.getItem("historialReparaciones");
  return datos === null ? [] : JSON.parse(datos);
}

function obtenerIntervenciones() {
  const datos = localStorage.getItem("historialIntervenciones");
  return datos === null ? [] : JSON.parse(datos);
}

function obtenerReemplazos() {
  const datos = localStorage.getItem("historialReemplazos");
  return datos === null ? [] : JSON.parse(datos);
}