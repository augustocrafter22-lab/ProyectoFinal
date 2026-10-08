const URL_API_PRESTAMOS = "../api/prestamos.php";
const URL_API_EQUIPOS = "../api/equipos.php";

const textos = document.querySelector("main").dataset;

const formularioPrestamo = document.getElementById("formularioPrestamo");
const cuerpoTablaPrestamos = document.getElementById("cuerpoTablaPrestamos");
const mensajePrestamos = document.getElementById("mensajePrestamos");
const filtroEstadoPrestamo = document.getElementById("filtroEstadoPrestamo");

const campoIdEquipo = document.getElementById("idEquipo");
const campoCedula = document.getElementById("cedulaSolicitante");
const campoFechaEstimada = document.getElementById("fechaDevolucionEstimada");

function mostrarMensaje(texto, esError) {
  mensajePrestamos.textContent = texto;
  mensajePrestamos.style.color = esError ? "red" : "green";
}

// Devuelve la fecha de hoy con formato AAAA-MM-DD, según la hora de la computadora.
function obtenerFechaHoy() {
  const hoy = new Date();
  const mes = String(hoy.getMonth() + 1).padStart(2, "0");
  const dia = String(hoy.getDate()).padStart(2, "0");

  return `${hoy.getFullYear()}-${mes}-${dia}`;
}

function crearBoton(texto, accion) {
  const boton = document.createElement("button");
  boton.type = "button";
  boton.textContent = texto;
  boton.addEventListener("click", accion);

  return boton;
}

function crearFilaPrestamo(prestamo) {
  const fila = document.createElement("tr");

  const celdaId = document.createElement("td");
  celdaId.textContent = prestamo.idPrestamo;

  const celdaEquipo = document.createElement("td");
  celdaEquipo.textContent = prestamo.idEquipo;

  const celdaSolicitante = document.createElement("td");
  celdaSolicitante.textContent = `${prestamo.cedulaSolicitante} - ${prestamo.solicitante}`;

  const celdaFechaPrestamo = document.createElement("td");
  celdaFechaPrestamo.textContent = prestamo.fechaPrestamo.slice(0, 16);

  // La devolución estimada se guarda hasta el final del día, por eso solo se muestra la fecha.
  const celdaFechaEstimada = document.createElement("td");
  celdaFechaEstimada.textContent = prestamo.fechaDevolucionEstimada.slice(0, 10);

  const celdaFechaReal = document.createElement("td");
  celdaFechaReal.textContent = prestamo.fechaDevolucionReal ? prestamo.fechaDevolucionReal.slice(0, 16) : "-";

  const celdaEstado = document.createElement("td");
  celdaEstado.textContent = prestamo.estado === "Activo" ? textos.textoEstadoActivo : textos.textoEstadoDevuelto;

  // Un préstamo activo se puede devolver, uno devuelto se puede eliminar.
  const celdaAcciones = document.createElement("td");

  if (prestamo.estado === "Activo") {
    celdaAcciones.appendChild(crearBoton(textos.textoDevolver, () => devolverPrestamo(prestamo.idPrestamo)));
  } else {
    celdaAcciones.appendChild(crearBoton(textos.textoEliminar, () => eliminarPrestamo(prestamo.idPrestamo)));
  }

  fila.append(celdaId, celdaEquipo, celdaSolicitante, celdaFechaPrestamo, celdaFechaEstimada, celdaFechaReal, celdaEstado, celdaAcciones);

  return fila;
}

// GET - Obtiene los préstamos, opcionalmente solo los de un estado.
async function obtenerPrestamos(estado) {
  const url = estado === "" ? URL_API_PRESTAMOS : `${URL_API_PRESTAMOS}?estado=${encodeURIComponent(estado)}`;
  const respuesta = await fetch(url);

  return await leerRespuestaAPI(respuesta);
}

// GET - Obtiene solo los equipos que están disponibles para prestar.
async function obtenerEquiposDisponibles() {
  const respuesta = await fetch(`${URL_API_EQUIPOS}?disponibilidad=Disponible`);

  return await leerRespuestaAPI(respuesta);
}

async function cargarPrestamos() {
  try {
    const prestamos = await obtenerPrestamos(filtroEstadoPrestamo.value);

    cuerpoTablaPrestamos.replaceChildren();
    for (const prestamo of prestamos) {
      cuerpoTablaPrestamos.appendChild(crearFilaPrestamo(prestamo));
    }
  } catch (error) {
    mostrarMensaje(error.message, true);
  }
}

// Vuelve a llenar la lista de equipos, que cambia con cada préstamo o devolución.
async function cargarEquiposDisponibles() {
  try {
    const equipos = await obtenerEquiposDisponibles();

    // Se conserva solo la primera opción ("Seleccione un equipo").
    campoIdEquipo.options.length = 1;

    for (const equipo of equipos) {
      const opcion = document.createElement("option");
      opcion.value = equipo.idEquipo;
      opcion.textContent = `${equipo.idEquipo} - ${equipo.marca} (${equipo.laboratorio})`;
      campoIdEquipo.appendChild(opcion);
    }

    if (equipos.length === 0) {
      mostrarMensaje(textos.textoSinEquipos, true);
    }
  } catch (error) {
    mostrarMensaje(error.message, true);
  }
}

async function registrarPrestamo(eventoFormulario) {
  eventoFormulario.preventDefault();

  const datosPrestamo = {
    idEquipo: campoIdEquipo.value,
    cedulaSolicitante: campoCedula.value.trim(),
    fechaDevolucionEstimada: campoFechaEstimada.value,
  };

  try {
    const respuesta = await fetch(URL_API_PRESTAMOS, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(datosPrestamo),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      formularioPrestamo.reset();
      cargarPrestamos();
    }

    // Se recarga siempre: si el equipo ya no estaba disponible, deja de aparecer en la lista.
    cargarEquiposDisponibles();
  } catch (error) {
    mostrarMensaje(textos.textoErrorConexion, true);
  }
}

// PUT - Registra la devolución del equipo de un préstamo activo.
async function devolverPrestamo(idPrestamo) {
  try {
    const respuesta = await fetch(`${URL_API_PRESTAMOS}?id=${encodeURIComponent(idPrestamo)}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ estado: "Devuelto" }),
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    cargarPrestamos();
    cargarEquiposDisponibles();
  } catch (error) {
    mostrarMensaje(textos.textoErrorConexion, true);
  }
}

// DELETE - Elimina un préstamo que ya fue devuelto.
async function eliminarPrestamo(idPrestamo) {
  try {
    const respuesta = await fetch(`${URL_API_PRESTAMOS}?id=${encodeURIComponent(idPrestamo)}`, {
      method: "DELETE",
      headers: { "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content },
    });
    const cuerpo = await respuesta.json();

    mostrarMensaje(cuerpo.mensaje, !cuerpo.exito);

    if (cuerpo.exito) {
      cargarPrestamos();
    }
  } catch (error) {
    mostrarMensaje(textos.textoErrorConexion, true);
  }
}

formularioPrestamo.addEventListener("submit", registrarPrestamo);
filtroEstadoPrestamo.addEventListener("change", cargarPrestamos);

// No se puede elegir una fecha de devolución anterior a hoy.
campoFechaEstimada.min = obtenerFechaHoy();

cargarPrestamos();
cargarEquiposDisponibles();
