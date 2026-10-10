const URL_API_EQUIPOS_ESTADO = "../api/equipos.php";

document.addEventListener("DOMContentLoaded", () => {
  const selectEquipo = document.getElementById("cambiarEstadoEquipoSelect");
  const formulario = document.getElementById("formCambiarEstado");

  if (!selectEquipo || !formulario) {
    return;
  }

  selectEquipo.addEventListener("change", cargarEstadoActual);
  formulario.addEventListener("submit", registrarCambioEstado);
});

async function obtenerEquipoEstado(idEquipo) {
  const respuesta = await fetch(
    `${URL_API_EQUIPOS_ESTADO}?id=${encodeURIComponent(idEquipo)}`,
  );
  const cuerpo = await respuesta.json();

  if (cuerpo.status !== "success") {
    throw new Error(cuerpo.message || "No se pudo obtener el equipo.");
  }

  return cuerpo.data;
}

async function cargarEstadoActual() {
  const idEquipo = document.getElementById("cambiarEstadoEquipoSelect").value;
  const campoEstadoActual = document.getElementById("cambiarEstadoActual");

  if (!idEquipo) {
    campoEstadoActual.value = "";
    return;
  }

  try {
    const equipo = await obtenerEquipoEstado(idEquipo);
    campoEstadoActual.value = equipo.estado;
  } catch (error) {
    mostrarMensajeEstado(error.message);
  }
}

async function registrarCambioEstado(evento) {
  evento.preventDefault();
  const idEquipo = document.getElementById("cambiarEstadoEquipoSelect").value;
  const estado = document.getElementById("cambiarEstadoNuevo").value;

  if (!idEquipo || !estado) {
    mostrarMensajeEstado("Seleccioná un equipo y el nuevo estado.");
    return;
  }

  try {
    const equipo = await obtenerEquipoEstado(idEquipo);
    if (equipo.estado === estado) {
      mostrarMensajeEstado("El equipo ya se encuentra en ese estado.");
      return;
    }

    const respuesta = await fetch(
      `${URL_API_EQUIPOS_ESTADO}?id=${encodeURIComponent(idEquipo)}`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')
            .content,
        },
        body: JSON.stringify({
          idLaboratorio: equipo.idLaboratorio,
          marca: equipo.marca,
          estado,
          disponibilidad: equipo.disponibilidad,
          informacion: equipo.informacion || "",
        }),
      },
    );
    const cuerpo = await respuesta.json();
    mostrarMensajeEstado(cuerpo.message);

    if (cuerpo.status === "success") {
      document.getElementById("formCambiarEstado").reset();
      document.getElementById("cambiarEstadoActual").value = "";
    }
  } catch (error) {
    mostrarMensajeEstado(
      error.message || "No se pudo conectar con el servidor.",
    );
  }
}

function mostrarMensajeEstado(mensaje) {
  alert(mensaje);
}
