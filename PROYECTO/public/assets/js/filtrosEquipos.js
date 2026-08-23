const filtroID = document.getElementById("filtroID");
const filtroEstado = document.getElementById("filtroEstado");
const filtroLab = document.getElementById("filtroLab");
const filtroDisponibilidad = document.getElementById("filtroDisponibilidad");
const cuerpoTablaPc = document.getElementById("cuerpoTablaPc");

function aplicarFiltrosEquipos() {
  const idBuscado = filtroID.value.trim().toUpperCase();
  const estadoBuscado = filtroEstado.value.trim().toUpperCase();
  const laboratorioBuscado = filtroLab.value.trim().toUpperCase();
  const disponibilidadBuscada = filtroDisponibilidad.value.trim().toUpperCase();
  const filas = cuerpoTablaPc.querySelectorAll("tr");

  filas.forEach(function (fila) {
    const celdas = fila.querySelectorAll("td");
    const coincideID = celdas[0].textContent
      .trim()
      .toUpperCase()
      .includes(idBuscado);
    const coincideLaboratorio = celdas[1].textContent
      .trim()
      .toUpperCase()
      .includes(laboratorioBuscado);
    const coincideEstado = celdas[3].textContent
      .trim()
      .toUpperCase()
      .includes(estadoBuscado);
    const coincideDisponibilidad = celdas[4].textContent
      .trim()
      .toUpperCase()
      .includes(disponibilidadBuscada);

    fila.style.display =
      coincideID &&
      coincideLaboratorio &&
      coincideEstado &&
      coincideDisponibilidad
        ? "table-row"
        : "none";
  });
}

filtroID.addEventListener("input", aplicarFiltrosEquipos);
filtroEstado.addEventListener("input", aplicarFiltrosEquipos);
filtroLab.addEventListener("input", aplicarFiltrosEquipos);
filtroDisponibilidad.addEventListener("input", aplicarFiltrosEquipos);
