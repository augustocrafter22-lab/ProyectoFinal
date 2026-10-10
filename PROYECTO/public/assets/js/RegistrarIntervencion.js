const URL_API_EQUIPOS_INTERVENCION = "../api/equipos.php";
const URL_API_INTERVENCIONES = "../api/intervenciones.php";

const formRegistrarIntervencion = document.getElementById(
  "formRegistrarIntervencion",
);
const selectEquipoIntervencion = document.getElementById(
  "intervencionEquipoSelect",
);
const mensajeIntervencion = document.getElementById("mensajeIntervencion");

function mostrarMensajeIntervencion(texto, esError) {
  mensajeIntervencion.textContent = texto;
  mensajeIntervencion.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarEquiposIntervencion() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS_INTERVENCION);
    const cuerpo = await respuesta.json();

    if (cuerpo.status !== "success") {
      mostrarMensajeIntervencion(cuerpo.message, true);
      return;
    }

    for (const equipo of cuerpo.data) {
      const option = document.createElement("option");
      option.value = equipo.idEquipo;
      option.textContent = equipo.idEquipo;
      selectEquipoIntervencion.appendChild(option);
    }
  } catch (error) {
    mostrarMensajeIntervencion("No se pudo conectar con el servidor.", true);
  }
}

async function registrarIntervencion(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosIntervencion = {
    idEquipo: selectEquipoIntervencion.value,
    tipo: document.getElementById("intervencionTipo").value.trim(),
    descripcion: document
      .getElementById("intervencionDescripcion")
      .value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_INTERVENCIONES, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')
          .content,
      },
      body: JSON.stringify(datosIntervencion),
    });
    const cuerpo = await respuesta.json();

    mostrarMensajeIntervencion(cuerpo.message, cuerpo.status !== "success");

    if (cuerpo.status === "success") {
      formRegistrarIntervencion.reset();
    }
  } catch (error) {
    mostrarMensajeIntervencion("No se pudo conectar con el servidor.", true);
  }
}

formRegistrarIntervencion.addEventListener("submit", registrarIntervencion);
cargarEquiposIntervencion();
