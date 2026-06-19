/* Controla la apertura/cierre del menú hamburguesa en pantallas angostas. */
const btnMenu = document.getElementById("btnMenu");
const btnCerrarMenu = document.getElementById("btnCerrarMenu");
const listaNavegacion = document.querySelector(".listaNavegacion");

/* Muestra el menú lateral y oculta el botón hamburguesa.
   Ambos botones no coexisten visibles para evitar conflictos de UX. */
function abrirMenu() {
  listaNavegacion.classList.add("visible");
  btnCerrarMenu.classList.add("visible");
  btnMenu.classList.add("oculto");
}

/* Revierte al estado inicial: menú cerrado, botón hamburguesa visible. */
function cerrarMenu() {
  listaNavegacion.classList.remove("visible");
  btnCerrarMenu.classList.remove("visible");
  btnMenu.classList.remove("oculto");
}

btnMenu.addEventListener("click", abrirMenu);
btnCerrarMenu.addEventListener("click", cerrarMenu);
