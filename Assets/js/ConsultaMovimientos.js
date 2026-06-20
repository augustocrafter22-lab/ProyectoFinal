document.addEventListener("DOMContentLoaded", iniciarMovimientosEquipo);

function iniciarMovimientosEquipo() {
  const selectEquipo = document.getElementById("movimientosEquipoSelect");
  selectEquipo.addEventListener("change", renderizarMovimientos);
}

function renderizarMovimientos() {
  const equipoId = document.getElementById("movimientosEquipoSelect").value;
  const tabla = document.getElementById("tablaMovimientos");

  if (!equipoId) {
    tabla.replaceChildren();
    return;
  }

  const historialEstados = obtenerHistorialEstados()
    .filter(function (r) { return r.equipoId === equipoId; })
    .map(function (r) {
      return {
        tipo: "Estado",
        anterior: r.estadoAnterior,
        nuevo: r.estadoNuevo,
        fecha: r.fecha,
        usuario: r.usuario
      };
    });

  const historialUbicaciones = obtenerHistorialUbicaciones()
    .filter(function (r) { return r.equipoId === equipoId; })
    .map(function (r) {
      return {
        tipo: "Ubicación",
        anterior: r.ubicacionAnterior,
        nuevo: r.ubicacionNueva,
        fecha: r.fecha,
        usuario: r.usuario
      };
    });

  const movimientos = historialEstados.concat(historialUbicaciones);

  movimientos.sort(function (a, b) {
    return new Date(b.fecha) - new Date(a.fecha);
  });

  if (movimientos.length === 0) {
    const cuerpo = document.createElement("tbody");
    const fila = document.createElement("tr");
    const celda = document.createElement("td");
    celda.textContent = "No hay movimientos registrados para este equipo.";
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
  const columnas = ["Tipo", "Anterior", "Nuevo", "Fecha", "Usuario"];

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

  movimientos.forEach(function (movimiento, indice) {
    const fila = document.createElement("tr");
    fila.style.backgroundColor = indice % 2 === 0 ? "#f6f7fc" : "#ffffff";

    const valores = [
      movimiento.tipo,
      movimiento.anterior,
      movimiento.nuevo,
      movimiento.fecha,
      movimiento.usuario || "-"
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



function obtenerHistorialEstados() {
  const datos = localStorage.getItem("historialEstados");
  return datos === null ? [] : JSON.parse(datos);
}

function obtenerHistorialUbicaciones() {
  const datos = localStorage.getItem("historialUbicaciones");
  return datos === null ? [] : JSON.parse(datos);
}