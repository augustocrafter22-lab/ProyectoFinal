const URL_API_DIAGNOSTICOS = "api/diagnosticos.php";
const URL_API_REPARACIONES = "api/reparaciones.php";

const formRegistrarReparacion = document.getElementById("formRegistrarReparacion");
const mensajeRegistrarReparacion = document.getElementById("mensajeRegistrarReparacion");
const avisoSinDiagnosticosReparacion = document.getElementById("avisoSinDiagnosticosReparacion");
const selectDiagnosticoReparacion = document.getElementById("registrarReparacionDiagnostico");
const campoTextoReparacion = document.getElementById("registrarReparacionTexto");

function mostrarMensaje(texto, esError) {
  mensajeRegistrarReparacion.textContent = texto;
  mensajeRegistrarReparacion.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarDiagnosticosEnFormulario() {
  try {
    const respuesta = await fetch(URL_API_DIAGNOSTICOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito || cuerpo.datos.length === 0) {
      avisoSinDiagnosticosReparacion.hidden = false;
      formRegistrarReparacion.hidden = true;
      return;
    }

    for (const diagnostico of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = diagnostico.idDiagnostico;
      option.textContent = `Ticket ${diagnostico.idTicket} - Equipo ${diagnostico.equipo} - ${diagnostico.diagnostico}`;
      selectDiagnosticoReparacion.appendChild(option);
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

async function registrarReparacion(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosReparacion = {
    idDiagnostico: selectDiagnosticoReparacion.value,
    cedulaTecnico: window.cedulaTecnico,
    reparacion: campoTextoReparacion.value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_REPARACIONES, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(datosReparacion),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      formRegistrarReparacion.reset();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

formRegistrarReparacion.addEventListener("submit", registrarReparacion);
cargarDiagnosticosEnFormulario();
