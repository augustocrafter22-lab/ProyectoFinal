/**
 * CONSTANTES Y VARIABLES NECESARIAS
 */

const btnAltaPc = document.getElementById("btnAltaPc");
const btnCerrarGestionarPc = document.getElementById("btnCerrarGestionarPc");
const btnMasInformacion = document.getElementById("btnMasInformacion");
const dialogGestionarPc = document.querySelector(".dialogGestionarPc");
const cuerpoTablaPc = document.getElementById("cuerpoTablaPc");
const formularioGestionarPc = document.getElementById("formularioGestionarPc");
const filtroID = document.getElementById("filtroID");

// Campos del formulario
const entradaID = document.getElementById("ID");
const entradaLab = document.getElementById("Lab");
const entradaEstado = document.getElementById("Estado");
const entradaMarca = document.getElementById("Marca");
const entradaInfo = document.getElementById("Info");

// Auxiliar para guardar datos vinculados a la modificacion de una PC
let pcEnEdicion = false;
let pcMasInformacion = false;
let pcMostrarInfo = false;

/**
 * GESTION DEL ESTADO DEL FORMULARIO/MODAL
 */

function limpiarEstadoGestionarPc() {
  pcEnEdicion = false;
  pcMostrarInfo = false;
  entradaID.readOnly = false;
  entradaLab.readOnly = false;
  entradaMarca.readOnly = false;
  entradaEstado.readOnly = false;
  entradaInfo.readOnly = false;
  formularioGestionarPc.reset();
}

function abrirAltaPc() {
  limpiarEstadoGestionarPc();
  dialogGestionarPc.showModal();
}

function abrirMasInfo(id) {
  pcMostrarInfo = true;

  const pcs = cargarPcsGuardadasLocal();
  const pcAMostrar = pcs.find((pc) => {
    return pc.id === id;
  });

  if (pcAMostrar === undefined) {
    return;
  }

  // Cargar los datos al formulario en modo lectura
  entradaID.value = pcAMostrar.id;
  entradaLab.value = pcAMostrar.lab;
  entradaEstado.value = pcAMostrar.estado;
  entradaMarca.value = pcAMostrar.marca;
  entradaInfo.value = pcAMostrar.info || "";

  // Hacer todos los campos readonly
  entradaID.readOnly = true;
  entradaLab.readOnly = true;
  entradaMarca.readOnly = true;
  entradaEstado.readOnly = true;
  entradaInfo.readOnly = true;

  dialogGestionarPc.showModal();
}

function cerrarGestionarPc() {
  limpiarEstadoGestionarPc();
  dialogGestionarPc.close();
}

function abrirModificarPc(id) {
  pcEnEdicion = true;

  const pcs = cargarPcsGuardadasLocal();
  const pcAModificar = pcs.find((pc) => {
    return pc.id === id;
  });

  if (pcAModificar === undefined) {
    return;
  }

  // Cargar los datos al formulario
  entradaID.value = pcAModificar.id;
  entradaLab.value = pcAModificar.lab;
  entradaEstado.value = pcAModificar.estado;
  entradaMarca.value = pcAModificar.marca;

  // Proteger el ID para que no se modifique
  entradaID.readOnly = true;

  dialogGestionarPc.showModal();
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
  const pc = {
    id: entradaID.value.trim(),
    lab: entradaLab.value.trim(),
    estado: entradaEstado.value.trim(),
    marca: entradaMarca.value.trim(),
    info: entradaInfo.value.trim(),
  };
  return pc;
}

/**
 * GESTION DE FILAS DE LA TABLA
 */

function agregarFilaPc(pc) {
  const fila = document.createElement("tr");

  // Creación de celdas
  const campoID = document.createElement("td");
  campoID.textContent = pc.id;

  const campoLab = document.createElement("td");
  campoLab.textContent = pc.lab;

  const campoMarca = document.createElement("td");
  campoMarca.textContent = pc.marca;

  const campoEstado = document.createElement("td");
  campoEstado.textContent = pc.estado;

  // Espacio para colocar los botones de operaciones
  const campoOperaciones = document.createElement("td");
  const cajaOperaciones = document.createElement("div");
  cajaOperaciones.classList.add("cajaOperaciones");

  // Botón Modificar
  const btnModificar = document.createElement("button");
  btnModificar.type = "button";
  btnModificar.textContent = "Modificar";
  btnModificar.classList.add("btnOperacion");
  btnModificar.addEventListener("click", () => {
    abrirModificarPc(pc.id);
  });

  // Botón Eliminar
  const btnEliminar = document.createElement("button");
  btnEliminar.type = "button";
  btnEliminar.textContent = "Eliminar";
  btnEliminar.classList.add("btnOperacion");
  btnEliminar.addEventListener("click", () => {
    eliminarPcLocal(pc.id);
  });

  //Boton mas info
  const btnMasInformacion = document.createElement("button");
  btnMasInformacion.type = "button";
  btnMasInformacion.textContent = "Mas Informacion";
  btnMasInformacion.classList.add("btnOperacion");
  btnMasInformacion.addEventListener("click", () => {
    abrirMasInfo(pc.id);
  });

  // Armado de la estructura DOM
  cajaOperaciones.appendChild(btnModificar);
  cajaOperaciones.appendChild(btnEliminar);
  cajaOperaciones.appendChild(btnMasInformacion);
  campoOperaciones.appendChild(cajaOperaciones);

  fila.appendChild(campoID);
  fila.appendChild(campoLab);
  fila.appendChild(campoMarca);
  fila.appendChild(campoEstado);
  fila.appendChild(campoOperaciones);

  cuerpoTablaPc.appendChild(fila);
}

function actualizarTabla() {
  cuerpoTablaPc.replaceChildren();
  const pcs = cargarPcsGuardadasLocal();
  for (const pc of pcs) {
    agregarFilaPc(pc);
  }
}

function eliminarPcLocal(id) {
  const pcs = cargarPcsGuardadasLocal();
  const pcsActualizadas = pcs.filter((pc) => {
    return pc.id !== id;
  });

  actualizarPcsLocal(pcsActualizadas);
  actualizarTabla();
}

function modificarPcLocal(pcEnFormulario) {
  const pcs = cargarPcsGuardadasLocal();
  const pcAModificar = pcs.find((pc) => {
    return pc.id === pcEnFormulario.id;
  });

  if (pcAModificar === undefined) {
    return;
  }

  pcAModificar.lab = pcEnFormulario.lab;
  pcAModificar.estado = pcEnFormulario.estado;
  pcAModificar.marca = pcEnFormulario.marca;
  pcAModificar.info = pcEnFormulario.info;

  actualizarPcsLocal(pcs);
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

  if (idExistente) {
    return;
  }

  pcs.push(pc);
  actualizarPcsLocal(pcs);
}

function gestionarPc(eventoFormulario) {
  eventoFormulario.preventDefault();

  // No hacer nada si solo se está mostrando información
  if (pcMostrarInfo) {
    cerrarGestionarPc();
    return;
  }

  const pc = obtenerDatosFormularioPc();

  if (!pcEnEdicion) {
    guardarPcLocal(pc);
  } else {
    modificarPcLocal(pc);
  }

  cerrarGestionarPc();
  actualizarTabla();
}

/**
 * FILTROS
 */

function aplicarFiltroID() {
  const idBuscado = filtroID.value.trim().toUpperCase();
  const filas = cuerpoTablaPc.querySelectorAll("tr");

  filas.forEach(function (fila) {
    const celdaID = fila.querySelector("td");
    const idFila = celdaID.textContent.trim().toUpperCase();

    if (idBuscado === "" || idFila.includes(idBuscado)) {
      fila.style.display = "table-row";
    } else {
      fila.style.display = "none";
    }
  });
}

/**
 * EVENTOS
 */

formularioGestionarPc.addEventListener("submit", gestionarPc);
btnAltaPc.addEventListener("click", abrirAltaPc);
btnCerrarGestionarPc.addEventListener("click", cerrarGestionarPc);
dialogGestionarPc.addEventListener("cancel", limpiarEstadoGestionarPc);
filtroID.addEventListener("input", aplicarFiltroID);

// Inicializar tabla al cargar la vista
actualizarTabla();
