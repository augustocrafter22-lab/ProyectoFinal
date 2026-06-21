/**
 * CONSTANTES Y VARIABLES NECESARIAS
 */

const btnAltaPc = document.getElementById("btnAltaPc");
const btnCerrarGestionarPc = document.getElementById("btnCerrarGestionarPc");
const dialogGestionarPc = document.querySelector(".dialogGestionarPc");
const cuerpoTablaPc = document.getElementById("cuerpoTablaPc");
const formularioGestionarPc = document.getElementById("formularioGestionarPc");

// Campos del formulario
const entradaID = document.getElementById("ID");
const entradaLab = document.getElementById("Lab");
const entradaTipo = document.getElementById("Tipo");
const entradaCategoria = document.getElementById("Categoria");

// Auxiliar para guardar datos vinculados a la modificacion de una PC
let pcEnEdicion = false;

/**
 * GESTION DEL ESTADO DEL FORMULARIO/MODAL
 */

function limpiarEstadoGestionarPc() {
  pcEnEdicion = false;
  entradaID.readOnly = false;
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
  entradaTipo.value = pcAModificar.tipo;
  entradaCategoria.value = pcAModificar.categoria;

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
    tipo: entradaTipo.value.trim(),
    categoria: entradaCategoria.value.trim(),
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

  const campoTipo = document.createElement("td");
  campoTipo.textContent = pc.tipo;

  const campoCategoria = document.createElement("td");
  campoCategoria.textContent = pc.categoria;

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

  // Armado de la estructura DOM
  cajaOperaciones.appendChild(btnModificar);
  cajaOperaciones.appendChild(btnEliminar);
  campoOperaciones.appendChild(cajaOperaciones);
  const campoMarca = document.createElement("td");
  campoMarca.textContent = pc.marca;
  const campoMarca = document.createElement("td");
  campoMarca.textContent = pc.marca;

  fila.appendChild(campoID);
  cuerpoTablaPc.appendChild(fila);

  fila.appendChild(campoID);
  fila.appendChild(campoLab);
  fila.appendChild(campoTipo);
  fila.appendChild(campoCategoria);
  fila.appendChild(campoOperaciones);

  cuerpoTablaPc.appendChild(fila);

  fila.appendChild(campoLab);
  cuerpoTablaPc.appendChild(fila);

  fila.appendChild(campoMarca);
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
  pcAModificar.tipo = pcEnFormulario.tipo;
  pcAModificar.categoria = pcEnFormulario.categoria;

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
 * EVENTOS
 */

formularioGestionarPc.addEventListener("submit", gestionarPc);
btnAltaPc.addEventListener("click", abrirAltaPc);
btnCerrarGestionarPc.addEventListener("click", cerrarGestionarPc);
dialogGestionarPc.addEventListener("cancel", limpiarEstadoGestionarPc);

// Inicializar tabla al cargar la vista
actualizarTabla();
