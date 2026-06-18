const btnMenu = document.getElementById("btnMenu");
const btnCerrarMenu = document.getElementById("btnCerrarMenu");
const listaNavegacion = document.querySelector(".listaNavegacion");

function abrirMenu() {
  listaNavegacion.classList.add("visible");
  btnCerrarMenu.classList.add("visible");
  btnMenu.classList.add("oculto");
}

function cerrarMenu() {
  listaNavegacion.classList.remove("visible");
  btnCerrarMenu.classList.remove("visible");
  btnMenu.classList.remove("oculto");
}

btnMenu.addEventListener("click", abrirMenu);
btnCerrarMenu.addEventListener("click", cerrarMenu);
