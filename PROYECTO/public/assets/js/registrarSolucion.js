const URL_API_DIAGNOSTICOS = "api/diagnosticos.php";
const URL_API_SOLUCIONES = "api/soluciones.php";

const formRegistrarSolucion = document.getElementById("formRegistrarSolucion");
const mensajeRegistrarSolucion = document.getElementById("mensajeRegistrarSolucion");
const avisoSinDiagnosticos = document.getElementById("avisoSinDiagnosticos");
const selectDiagnostico = document.getElementById("registrarSolucionDiagnostico");
const campoSolucion = document.getElementById("registrarSolucionSolucion");

function mostrarMensaje(texto, esError) {
  mensajeRegistrarSolucion.textContent = texto;
  mensajeRegistrarSolucion.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarDiagnosticosEnFormulario() {
  try {
    const respuesta = await fetch(URL_API_DIAGNOSTICOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito || cuerpo.datos.length === 0) {
      avisoSinDiagnosticos.hidden = false;
      formRegistrarSolucion.hidden = true;
      return;
    }

    for (const diagnostico of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = diagnostico.idDiagnostico;
      option.textContent = `Ticket ${diagnostico.idTicket} - Equipo ${diagnostico.equipo} - ${diagnostico.diagnostico}`;
      selectDiagnostico.appendChild(option);
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

async function registrarSolucion(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosSolucion = {
    idDiagnostico: selectDiagnostico.value,
    cedulaTecnico: window.cedulaTecnico,
    solucion: campoSolucion.value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_SOLUCIONES, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datosSolucion),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      formRegistrarSolucion.reset();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

formRegistrarSolucion.addEventListener("submit", registrarSolucion);
cargarDiagnosticosEnFormulario();
