const URL_API_EQUIPOS_REEMPLAZO = "../api/equipos.php";
const URL_API_REEMPLAZOS = "../api/reemplazos.php";

const formRegistrarReemplazo = document.getElementById(
  "formRegistrarReemplazo",
);
const selectEquipoReemplazo = document.getElementById("reemplazoEquipoSelect");
const mensajeReemplazo = document.getElementById("mensajeReemplazo");

function mostrarMensajeReemplazo(texto, esError) {
  mensajeReemplazo.textContent = texto;
  mensajeReemplazo.className = esError ? "mensaje-error" : "mensaje-exito";
}

async function cargarEquiposReemplazo() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS_REEMPLAZO);
    const cuerpo = await respuesta.json();

    if (cuerpo.status !== "success") {
      mostrarMensajeReemplazo(cuerpo.message, true);
      return;
    }

    for (const equipo of cuerpo.data) {
      const option = document.createElement("option");
      option.value = equipo.idEquipo;
      option.textContent = equipo.idEquipo;
      selectEquipoReemplazo.appendChild(option);
    }
  } catch (error) {
    mostrarMensajeReemplazo("No se pudo conectar con el servidor.", true);
  }
}

async function registrarReemplazo(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosReemplazo = {
    idEquipo: selectEquipoReemplazo.value,
    componente: document.getElementById("reemplazoComponente").value.trim(),
    descripcion: document.getElementById("reemplazoDescripcion").value.trim(),
  };

  try {
    const respuesta = await fetch(URL_API_REEMPLAZOS, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')
          .content,
      },
      body: JSON.stringify(datosReemplazo),
    });
    const cuerpo = await respuesta.json();

    mostrarMensajeReemplazo(cuerpo.message, cuerpo.status !== "success");

    if (cuerpo.status === "success") {
      formRegistrarReemplazo.reset();
    }
  } catch (error) {
    mostrarMensajeReemplazo("No se pudo conectar con el servidor.", true);
  }
}

formRegistrarReemplazo.addEventListener("submit", registrarReemplazo);
cargarEquiposReemplazo();
