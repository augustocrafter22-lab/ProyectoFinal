const URL_API_TICKETS = "api/tickets.php";
const URL_API_DIAGNOSTICOS = "api/diagnosticos.php";

const formRegistrarDiagnostico = document.getElementById("formregistrarDiagnostico");
const mensajeRegistrarDiagnostico = document.getElementById("mensajeRegistrarDiagnostico");
const selectTicket = document.getElementById("registrarDiagnosticoTicket");
const campoDiagnostico = document.getElementById("registrarDiagnosticoDiagnostico");

function mostrarMensaje(texto, esError) {
  mensajeRegistrarDiagnostico.textContent = texto;
  mensajeRegistrarDiagnostico.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarTicketsEnFormulario() {
  try {
    const respuesta = await fetch(URL_API_TICKETS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      return;
    }

    for (const ticket of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = ticket.idTicket;
      option.textContent = `${ticket.idTicket} - ${ticket.asunto}`;
      selectTicket.appendChild(option);
    }
  } catch (error) {
    // Si no se pueden cargar los tickets, el select queda solo con la opción por defecto.
  }
}

async function registrarDiagnostico(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosDiagnostico = {
    idTicket: selectTicket.value,
    cedulaTecnico: window.cedulaTecnico,
    diagnostico: campoDiagnostico.value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_DIAGNOSTICOS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datosDiagnostico),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      formRegistrarDiagnostico.reset();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

formRegistrarDiagnostico.addEventListener("submit", registrarDiagnostico);
cargarTicketsEnFormulario();
