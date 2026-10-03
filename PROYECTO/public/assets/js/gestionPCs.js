const URL_API_EQUIPOS = "api/equipos.php";

const formularioEquipo = document.getElementById("formularioEquipo");
const cuerpoTablaPc = document.getElementById("cuerpoTablaPc");
const mensajeEquipos = document.getElementById("mensajeEquipos");
const leyendaFormularioEquipo = document.getElementById("leyendaFormularioEquipo");
const modoFormularioEquipo = document.getElementById("modoFormularioEquipo");
const btnCancelarEdicionEquipo = document.getElementById("btnCancelarEdicionEquipo");

const campoIdEquipo = document.getElementById("idEquipo");
const campoIdLaboratorio = document.getElementById("idLaboratorio");
const campoMarca = document.getElementById("marca");
const campoEstado = document.getElementById("estado");
const campoDisponibilidad = document.getElementById("disponibilidad");
const campoInformacion = document.getElementById("informacion");

function mostrarMensaje(texto, esError) {
  mensajeEquipos.textContent = texto;
  mensajeEquipos.style.color = esError ? "red" : "green";
}

function crearFilaEquipo(equipo) {
  const fila = document.createElement("tr");

  const celdaId = document.createElement("td");
  celdaId.textContent = equipo.idEquipo;

  const celdaLaboratorio = document.createElement("td");
  celdaLaboratorio.textContent = equipo.laboratorio;

  const celdaMarca = document.createElement("td");
  celdaMarca.textContent = equipo.marca;

  const celdaEstado = document.createElement("td");
  celdaEstado.textContent = equipo.estado;

  const celdaDisponibilidad = document.createElement("td");
  celdaDisponibilidad.textContent = equipo.disponibilidad;

  const celdaInformacion = document.createElement("td");
  celdaInformacion.textContent = equipo.informacion || "";

  const celdaAcciones = document.createElement("td");

  const btnModificar = document.createElement("button");
  btnModificar.type = "button";
  btnModificar.textContent = "Modificar";
  btnModificar.addEventListener("click", () => abrirEdicionEquipo(equipo.idEquipo));

  const btnEliminar = document.createElement("button");
  btnEliminar.type = "button";
  btnEliminar.textContent = "Eliminar";
  btnEliminar.addEventListener("click", () => eliminarEquipo(equipo.idEquipo));

  celdaAcciones.appendChild(btnModificar);
  celdaAcciones.appendChild(btnEliminar);

  fila.append(celdaId, celdaLaboratorio, celdaMarca, celdaEstado, celdaDisponibilidad, celdaInformacion, celdaAcciones);

  return fila;
}

async function cargarEquipos() {
  try {
    const respuesta = await fetch(URL_API_EQUIPOS);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      mostrarMensaje(cuerpo.mensaje, true);
      return;
    }

    cuerpoTablaPc.replaceChildren();
    for (const equipo of cuerpo.datos) {
      cuerpoTablaPc.appendChild(crearFilaEquipo(equipo));
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

function limpiarFormularioEquipo() {
  formularioEquipo.reset();
  modoFormularioEquipo.value = "alta";
  leyendaFormularioEquipo.textContent = "Alta de equipo";
  campoIdEquipo.readOnly = false;
  btnCancelarEdicionEquipo.hidden = true;

  const url = new URL(window.location.href);
  url.searchParams.delete("editar");
  window.history.replaceState({}, "", url);
}

async function abrirEdicionEquipo(idEquipo) {
  try {
    const respuesta = await fetch(`${URL_API_EQUIPOS}?id=${encodeURIComponent(idEquipo)}`);
    const cuerpo = await respuesta.json();

    if (!cuerpo.exito) {
      mostrarMensaje(cuerpo.mensaje, true);
      return;
    }

    const equipo = cuerpo.datos;

    modoFormularioEquipo.value = "modificar";
    leyendaFormularioEquipo.textContent = "Modificar equipo";
    campoIdEquipo.value = equipo.idEquipo;
    campoIdEquipo.readOnly = true;
    campoIdLaboratorio.value = equipo.idLaboratorio;
    campoMarca.value = equipo.marca;
    campoEstado.value = equipo.estado;
    campoDisponibilidad.value = equipo.disponibilidad;
    campoInformacion.value = equipo.informacion || "";
    btnCancelarEdicionEquipo.hidden = false;

    const url = new URL(window.location.href);
    url.searchParams.set("editar", idEquipo);
    window.history.replaceState({}, "", url);
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

async function eliminarEquipo(idEquipo) {
  try {
    const respuesta = await fetch(`${URL_API_EQUIPOS}?id=${encodeURIComponent(idEquipo)}`, {
      method: "DELETE",
      headers: { "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content },
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      cargarEquipos();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

async function guardarEquipo(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosEquipo = {
    idEquipo: campoIdEquipo.value.trim(),
    idLaboratorio: campoIdLaboratorio.value,
    marca: campoMarca.value,
    estado: campoEstado.value,
    disponibilidad: campoDisponibilidad.value,
    informacion: campoInformacion.value.trim(),
  };

  const esModificacion = modoFormularioEquipo.value === "modificar";
  const url = esModificacion ? `${URL_API_EQUIPOS}?id=${encodeURIComponent(datosEquipo.idEquipo)}` : URL_API_EQUIPOS;

  try {
    const respuesta = await fetch(url, {
      method: esModificacion ? "PUT" : "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(datosEquipo),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      limpiarFormularioEquipo();
      cargarEquipos();
    }
  } catch (error) {
    mostrarMensaje("No se pudo conectar con el servidor.", true);
  }
}

formularioEquipo.addEventListener("submit", guardarEquipo);
btnCancelarEdicionEquipo.addEventListener("click", limpiarFormularioEquipo);

const idEquipoParaEditar = new URLSearchParams(window.location.search).get("editar");

cargarEquipos();

if (idEquipoParaEditar) {
  abrirEdicionEquipo(idEquipoParaEditar);
}
