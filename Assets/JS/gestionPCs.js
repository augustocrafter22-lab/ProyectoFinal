/**
 * CONSTANTES Y VARIABLES NECESARIAS
 */

const btnAltaPc = document.getElementById("btnAltaPc");
const btnCerrarGestionarPc = document.getElementById("btnCerrarGestionarPc");
const dialogGestionarPc = document.querySelector(".dialogGestionarPc");
const cuerpoTablaPc = document.getElementById("cuerpoTablaPc");
const formularioGestionarPc = document.getElementById("formularioGestionarPc");
const entradaID = document.getElementById("ID");

/**
 * GESTION DEL ESTADO DEL FORMULARIO/MODAL
 */

function limpiarEstadoGestionarPc() {
  formularioGestionarPc.reset();
}

function abrirAltaPc() {
  limpiarEstadoGestionarPc();
  dialogGestionarPc.showModal();
}

function cerrarGestionarPc() {
  limpiarEstadoGestionarPc();
  dialogGestionarPc.close();
}

/**
 * OBTENCION Y RECUPERACION DE DATOS
 */

function cargarPcsGuardadasLocal() {
  const pcsGuardadas = localStorage.getItem("pcs");

  if (pcsGuardadas === null) return [];

  return JSON.parse(pcsGuardadas);
}

function obtenerDatosFormularioPc() {
  return {
    id: entradaID.value.trim(),
  };
}

/**
 * GESTION DE FILAS DE LA TABLA
 */

function agregarFilaPc(pc) {
  const fila = document.createElement("tr");

  const campoID = document.createElement("td");
  campoID.textContent = pc.id;

  fila.appendChild(campoID);
  cuerpoTablaPc.appendChild(fila);
}

function actualizarTabla() {
  cuerpoTablaPc.replaceChildren();

  const pcs = cargarPcsGuardadasLocal();

  for (const pc of pcs) {
    agregarFilaPc(pc);
  }
}

/**
 * FUNCIONALIDADES PRINCIPALES
 */

function actualizarPcsLocal(pcs) {
  localStorage.setItem("pcs", JSON.stringify(pcs));
}

function guardarPcLocal(pc) {
  const pcs = cargarPcsGuardadasLocal();

  const idExistente = pcs.some((pcGuardada) => {
    return pcGuardada.id === pc.id;
  });

  // Sale de la función si el ID ya existe
  if (idExistente) {
    return;
  }

  pcs.push(pc);
  actualizarPcsLocal(pcs);
}

function gestionarPc(eventoFormulario) {
  eventoFormulario.preventDefault();

  const pc = obtenerDatosFormularioPc();

  guardarPcLocal(pc);
  cerrarGestionarPc();
  actualizarTabla();
}

/**
 * EVENTOS
 */

formularioGestionarPc.addEventListener("submit", gestionarPc);
btnAltaPc.addEventListener("click", abrirAltaPc);
btnCerrarGestionarPc.addEventListener("click", cerrarGestionarPc);
dialogGestionarPc.addEventListener("cancel", limpiarEstadoGestionarPc);

// Carga inicial
actualizarTabla();
