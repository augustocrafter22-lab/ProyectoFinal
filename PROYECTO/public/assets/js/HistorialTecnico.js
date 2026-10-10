const URL_API_EQUIPOS = "../api/equipos.php";
const URL_API_REPARACIONES = "../api/reparaciones.php";

const selectEquipo = document.getElementById("historialTecnicoEquipoSelect");
const tablaHistorialTecnico = document.getElementById("tablaHistorialTecnico");

function mostrarTablaVacia(mensaje) {
  const cuerpo = document.createElement("tbody");
  const fila = document.createElement("tr");
  const celda = document.createElement("td");
  celda.textContent = mensaje;
  celda.colSpan = 4;
  celda.style.textAlign = "center";
  celda.style.padding = "16px";
  celda.style.color = "#6b7280";
  fila.appendChild(celda);
  cuerpo.appendChild(fila);
  tablaHistorialTecnico.replaceChildren(cuerpo);
}

function mostrarReparaciones(reparaciones) {
  if (reparaciones.length === 0) {
    mostrarTablaVacia("No hay reparaciones registradas para este equipo.");
    return;
  }

  const encabezado = document.createElement("thead");
  const filaEncabezado = document.createElement("tr");

  for (const nombre of ["Ticket", "Descripción", "Fecha", "Técnico"]) {
    const th = document.createElement("th");
    th.textContent = nombre;
    th.style.textAlign = "left";
    th.style.padding = "8px 10px";
    th.style.borderBottom = "2px solid #1956c0";
    th.style.color = "#153894";
    th.style.fontSize = "13px";
    filaEncabezado.appendChild(th);
  }

  encabezado.appendChild(filaEncabezado);

  const cuerpo = document.createElement("tbody");

  reparaciones.forEach((reparacion, indice) => {
    const fila = document.createElement("tr");
    fila.style.backgroundColor = indice % 2 === 0 ? "#f6f7fc" : "#ffffff";

    const valores = [reparacion.idTicket, reparacion.reparacion, reparacion.fechaReparacion, reparacion.cedulaTecnico];

    for (const valor of valores) {
      const td = document.createElement("td");
      td.textContent = valor || "-";
      td.style.padding = "8px 10px";
      td.style.borderBottom = "1px solid #d7dbe8";
      td.style.fontSize = "13px";
      fila.appendChild(td);
    }

    cuerpo.appendChild(fila);
  });

  tablaHistorialTecnico.replaceChildren(encabezado, cuerpo);
}

async function cargarHistorialTecnico() {
  const idEquipo = selectEquipo.value;

  const url = new URL(window.location.href);
  if (idEquipo !== "") {
    url.searchParams.set("equipo", idEquipo);
  } else {
    url.searchParams.delete("equipo");
  }
  window.history.replaceState({}, "", url);

  if (idEquipo === "") {
    tablaHistorialTecnico.replaceChildren();
    return;
  }

  try {
    const respuesta = await fetch(`${URL_API_REPARACIONES}?idEquipo=${encodeURIComponent(idEquipo)}`);
    const cuerpo = await respuesta.json();

    if (cuerpo.status !== "success") {
      mostrarTablaVacia(cuerpo.message);
      return;
    }

    mostrarReparaciones(cuerpo.data);
  } catch (error) {
    mostrarTablaVacia("No se pudo conectar con el servidor.");
  }
}

async function cargarEquiposEnSelect() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS);
    const cuerpo = await respuesta.json();

    if (cuerpo.status !== "success") {
      return;
    }

    const idEquipoInicial = new URLSearchParams(window.location.search).get("equipo") || "";

    for (const equipo of cuerpo.data) {
      const option = document.createElement("option");
      option.value = equipo.idEquipo;
      option.textContent = equipo.idEquipo;
      option.selected = equipo.idEquipo === idEquipoInicial;
      selectEquipo.appendChild(option);
    }

    if (idEquipoInicial !== "") {
      cargarHistorialTecnico();
    }
  } catch (error) {
    // Si no se pueden cargar los equipos, el select queda solo con la opción por defecto.
  }
}

selectEquipo.addEventListener("change", cargarHistorialTecnico);
cargarEquiposEnSelect();
