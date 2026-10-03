const URL_API_TICKETS = "api/tickets.php";
const URL_API_EQUIPOS = "api/equipos.php";

const ticketForm = document.getElementById("ticketForm");
const mensajeIngresoTickets = document.getElementById("mensajeIngresoTickets");
const selectEquipo = document.getElementById("equipo");

function mostrarMensaje(texto, esError) {
  mensajeIngresoTickets.textContent = texto;
  mensajeIngresoTickets.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarEquiposEnFormulario() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      return;
    }

    for (const equipo of cuerpo.datos) {
      const option = document.createElement("option");
      option.value = equipo.idEquipo;
      option.textContent = equipo.idEquipo;
      selectEquipo.appendChild(option);
    }
  } catch (error) {
    // Si no se pueden cargar los equipos, el select queda solo con la opción por defecto.
  }
}

async function enviarTicket(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosTicket = {
    laboratorio: document.getElementById("laboratorioTaller").value,
    equipo: selectEquipo.value,
    asunto: document.getElementById("asunto").value.trim(),
    descripcion: document.getElementById("descripcion").value.trim(),
    turno: document.getElementById("turno").value,
    grupo: document.getElementById("grupo").value.trim(),
    profesor: document.getElementById("profesor").value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_TICKETS, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(datosTicket),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      ticketForm.reset();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

ticketForm.addEventListener("submit", enviarTicket);
cargarEquiposEnFormulario();
