const URL_API_DIAGNOSTICOS = "api/diagnosticos.php";

const formModificarDiagnostico = document.getElementById("formModificarDiagnostico");
const mensajeModificarDiagnostico = document.getElementById("mensajeModificarDiagnostico");
const selectDiagnostico = document.getElementById("modificarDiagnosticoSelect");
const campoTextoDiagnostico = document.getElementById("modificarDiagnosticoTexto");

function mostrarMensaje(texto, esError) {
  mensajeModificarDiagnostico.textContent = texto;
  mensajeModificarDiagnostico.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarDiagnosticosEnFormulario() {
  try {
    const respuesta = await fetch(URL_API_DIAGNOSTICOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      return;
    }

    for (const diagnostico of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = diagnostico.idDiagnostico;
      option.dataset.texto = diagnostico.diagnostico;
      option.textContent = `${diagnostico.idDiagnostico} | ${diagnostico.idTicket} | ${diagnostico.fechaDiagnostico}`;
      selectDiagnostico.appendChild(option);
    }
  } catch (error) {
    // Si no se pueden cargar los diagnósticos, el select queda solo con la opción por defecto.
  }
}

function completarTextoDiagnostico() {
  const opcionSeleccionada = selectDiagnostico.options[selectDiagnostico.selectedIndex];
  campoTextoDiagnostico.value = opcionSeleccionada.dataset.texto || "";
}

async function modificarDiagnostico(eventoFormulario) {
  eventoFormulario.preventDefault();

  const idDiagnostico = selectDiagnostico.value;

  try {
    const respuesta = await fetch(`${URL_API_DIAGNOSTICOS}?id=${encodeURIComponent(idDiagnostico)}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ diagnostico: campoTextoDiagnostico.value.trim() }),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

selectDiagnostico.addEventListener("change", completarTextoDiagnostico);
formModificarDiagnostico.addEventListener("submit", modificarDiagnostico);
cargarDiagnosticosEnFormulario();
