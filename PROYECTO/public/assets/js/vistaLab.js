const filtroPorFecha = document.getElementById("filtroPorFecha");
const filtroPorLaboratorio = document.getElementById("filtroPorLaboratorio");
const btnLimpiarFiltro = document.getElementById("btnLimpiarFiltro");
const cuerpoTabla = document.getElementById("cuerpoTabla");

// Columnas de la tabla de solicitudes que se usan para filtrar.
const COLUMNA_LABORATORIO = 1;
const COLUMNA_FECHA = 5;

function aplicarFiltrosLaboratorio() {
  const fechaBuscada = filtroPorFecha.value;
  const laboratorioBuscado = filtroPorLaboratorio.value;
  const filas = cuerpoTabla.querySelectorAll("tr");

  filas.forEach(function (fila) {
    const celdas = fila.querySelectorAll("td");
    const laboratorio = celdas[COLUMNA_LABORATORIO].textContent.trim();
    const fecha = celdas[COLUMNA_FECHA].textContent.trim();

    const coincideFecha = fechaBuscada === "" || fecha === fechaBuscada;
    const coincideLaboratorio = laboratorioBuscado === "" || laboratorio === laboratorioBuscado;

    fila.style.display = coincideFecha && coincideLaboratorio ? "table-row" : "none";
  });
}

function limpiarFiltrosLaboratorio() {
  filtroPorFecha.value = "";
  filtroPorLaboratorio.value = "";
  aplicarFiltrosLaboratorio();
}

filtroPorFecha.addEventListener("change", aplicarFiltrosLaboratorio);
filtroPorLaboratorio.addEventListener("change", aplicarFiltrosLaboratorio);
btnLimpiarFiltro.addEventListener("click", limpiarFiltrosLaboratorio);
