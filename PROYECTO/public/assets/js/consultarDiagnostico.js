const URL_API_DIAGNOSTICOS = "../api/diagnosticos.php";

const formFiltroDiagnosticos = document.getElementById("formFiltroDiagnosticos");
const campoFiltroTicket = document.getElementById("filtroTicket");
const btnQuitarFiltroTicket = document.getElementById("btnQuitarFiltroTicket");
const tituloConsultarDiagnosticos = document.getElementById("tituloConsultarDiagnosticos");
const tablaDiagnosticos = document.getElementById("tablaDiagnosticos");
const cuerpoTablaDiagnosticos = document.getElementById("cuerpoTablaDiagnosticos");

function crearFilaDiagnostico(diagnostico, indice) {
  const fila = document.createElement("tr");
  fila.classList.add(indice % 2 === 0 ? "tabla-fila-par" : "tabla-fila-impar");

  for (const valor of [diagnostico.idDiagnostico, diagnostico.idTicket, diagnostico.diagnostico, diagnostico.fechaDiagnostico, diagnostico.cedulaTecnico]) {
    const celda = document.createElement("td");
    celda.classList.add("tabla-td");
    celda.textContent = valor;
    fila.appendChild(celda);
  }

  return fila;
}

function mostrarDiagnosticosVacio(mensaje) {
  tablaDiagnosticos.querySelector("thead").hidden = true;
  cuerpoTablaDiagnosticos.replaceChildren();

  const fila = document.createElement("tr");
  const celda = document.createElement("td");
  celda.colSpan = 5;
  celda.classList.add("tabla-vacia");
  celda.textContent = mensaje;
  fila.appendChild(celda);
  cuerpoTablaDiagnosticos.appendChild(fila);
}

async function cargarDiagnosticos() {
  const ticketFiltro = new URLSearchParams(window.location.search).get("ticket") || "";

  campoFiltroTicket.value = ticketFiltro;
  btnQuitarFiltroTicket.hidden = ticketFiltro === "";
  tituloConsultarDiagnosticos.textContent = ticketFiltro !== ""
    ? `Diagnósticos del ticket ${ticketFiltro}`
    : "Diagnósticos registrados";

  const url = ticketFiltro !== ""
    ? `${URL_API_DIAGNOSTICOS}?idTicket=${encodeURIComponent(ticketFiltro)}`
    : URL_API_DIAGNOSTICOS;

  try {
    const respuesta = await fetch(url);
    const cuerpo = await respuesta.json();

    if (cuerpo.status !== "success") {
      mostrarDiagnosticosVacio(cuerpo.message);
      return;
    }

    if (cuerpo.data.length === 0) {
      mostrarDiagnosticosVacio(
        ticketFiltro !== ""
          ? `No hay diagnósticos registrados para el ticket ${ticketFiltro}.`
          : "No hay diagnósticos registrados."
      );
      return;
    }

    tablaDiagnosticos.querySelector("thead").hidden = false;
    cuerpoTablaDiagnosticos.replaceChildren();
    cuerpo.data.forEach((diagnostico, indice) => {
      cuerpoTablaDiagnosticos.appendChild(crearFilaDiagnostico(diagnostico, indice));
    });
  } catch (error) {
    mostrarDiagnosticosVacio("No se pudo conectar con el servidor.");
  }
}

formFiltroDiagnosticos.addEventListener("submit", evento => {
  evento.preventDefault();

  const url = new URL(window.location.href);
  if (campoFiltroTicket.value.trim() !== "") {
    url.searchParams.set("ticket", campoFiltroTicket.value.trim());
  } else {
    url.searchParams.delete("ticket");
  }
  window.history.replaceState({}, "", url);

  cargarDiagnosticos();
});

btnQuitarFiltroTicket.addEventListener("click", () => {
  const url = new URL(window.location.href);
  url.searchParams.delete("ticket");
  window.history.replaceState({}, "", url);

  cargarDiagnosticos();
});

cargarDiagnosticos();
