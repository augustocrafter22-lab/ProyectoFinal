const URL_API_EQUIPOS_UBICACION = "../api/equipos.php";
const URL_API_LABORATORIOS_UBICACION = "../api/laboratorios.php";

document.addEventListener("DOMContentLoaded", () => {
  const selectEquipo = document.getElementById("cambiarUbicacionEquipoSelect");
  const formulario = document.getElementById("formCambiarUbicacion");

  if (!selectEquipo || !formulario) {
    return;
  }

  selectEquipo.addEventListener("change", cargarUbicacionActual);
  formulario.addEventListener("submit", registrarCambioUbicacion);
  cargarLaboratoriosUbicacion();
});

async function cargarLaboratoriosUbicacion() {
  const campoLaboratorio = document.getElementById("cambiarUbicacionNueva");
  if (campoLaboratorio.tagName !== "SELECT") {
    return;
  }

  try {
    const respuesta = await fetch(URL_API_LABORATORIOS_UBICACION);
    const cuerpo = await respuesta.json();
    if (cuerpo.status !== "success") {
      mostrarMensajeUbicacion(
        cuerpo.message || "No se pudieron cargar los laboratorios.",
      );
      return;
    }

    for (const laboratorio of cuerpo.data) {
      const opcion = document.createElement("option");
      opcion.value = laboratorio.idLaboratorio;
      opcion.textContent = `${laboratorio.numeroLaboratorio} (${laboratorio.idLaboratorio})`;
      campoLaboratorio.appendChild(opcion);
    }
  } catch (error) {
    mostrarMensajeUbicacion("No se pudieron cargar los laboratorios.");
  }
}

async function obtenerEquipoUbicacion(idEquipo) {
  const respuesta = await fetch(
    `${URL_API_EQUIPOS_UBICACION}?id=${encodeURIComponent(idEquipo)}`,
  );
  const cuerpo = await respuesta.json();

  if (cuerpo.status !== "success") {
    throw new Error(cuerpo.message || "No se pudo obtener el equipo.");
  }

  return cuerpo.data;
}

async function resolverIdLaboratorio(valor) {
  const campoLaboratorio = document.getElementById("cambiarUbicacionNueva");
  if (campoLaboratorio.tagName === "SELECT") {
    return valor;
  }

  const respuesta = await fetch(URL_API_LABORATORIOS_UBICACION);
  const cuerpo = await respuesta.json();
  if (cuerpo.status !== "success") {
    throw new Error(
      cuerpo.message || "No se pudieron cargar los laboratorios.",
    );
  }

  const laboratorio = cuerpo.data.find(
    (item) =>
      item.idLaboratorio === valor || String(item.numeroLaboratorio) === valor,
  );
  if (!laboratorio) {
    throw new Error("Ingresá un ID o número de laboratorio válido.");
  }

  return laboratorio.idLaboratorio;
}

async function cargarUbicacionActual() {
  const idEquipo = document.getElementById(
    "cambiarUbicacionEquipoSelect",
  ).value;
  const campoUbicacionActual = document.getElementById(
    "cambiarUbicacionActual",
  );

  if (!idEquipo) {
    campoUbicacionActual.value = "";
    return;
  }

  try {
    const equipo = await obtenerEquipoUbicacion(idEquipo);
    campoUbicacionActual.value = equipo.laboratorio;
    const campoLaboratorioNuevo = document.getElementById(
      "cambiarUbicacionNueva",
    );
    if (campoLaboratorioNuevo.tagName === "SELECT") {
      campoLaboratorioNuevo.value = equipo.idLaboratorio;
    }
  } catch (error) {
    mostrarMensajeUbicacion(error.message);
  }
}

async function registrarCambioUbicacion(evento) {
  evento.preventDefault();
  const idEquipo = document.getElementById(
    "cambiarUbicacionEquipoSelect",
  ).value;
  const idLaboratorio = document
    .getElementById("cambiarUbicacionNueva")
    .value.trim();

  if (!idEquipo || !idLaboratorio) {
    mostrarMensajeUbicacion(
      "Seleccioná un equipo e indicá el nuevo laboratorio.",
    );
    return;
  }

  try {
    const equipo = await obtenerEquipoUbicacion(idEquipo);
    const nuevoIdLaboratorio = await resolverIdLaboratorio(idLaboratorio);
    if (equipo.idLaboratorio === nuevoIdLaboratorio) {
      mostrarMensajeUbicacion("El equipo ya se encuentra en ese laboratorio.");
      return;
    }

    const respuesta = await fetch(
      `${URL_API_EQUIPOS_UBICACION}?id=${encodeURIComponent(idEquipo)}`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')
            .content,
        },
        body: JSON.stringify({
          idLaboratorio: nuevoIdLaboratorio,
          marca: equipo.marca,
          estado: equipo.estado,
          disponibilidad: equipo.disponibilidad,
          informacion: equipo.informacion || "",
        }),
      },
    );
    const cuerpo = await respuesta.json();
    mostrarMensajeUbicacion(cuerpo.message);

    if (cuerpo.status === "success") {
      document.getElementById("formCambiarUbicacion").reset();
      document.getElementById("cambiarUbicacionActual").value = "";
    }
  } catch (error) {
    mostrarMensajeUbicacion(
      error.message || "No se pudo conectar con el servidor.",
    );
  }
}

function mostrarMensajeUbicacion(mensaje) {
  alert(mensaje);
}
