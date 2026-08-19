// Dialog para gestionar usuarios
const dialog = document.querySelector(".dialogGestionarUsuario");
const btnAbrirDialog = document.getElementById("btnAltaUsuario");
const btnCerrarDialog = document.getElementById("btnCerrarGestionarUsuario");


// Tabla de usuarios
const cuerpoTablaUsuarios = document.getElementById("cuerpoTablaUsuarios");

// Formulario de gestión de usuarios
const formulario = document.getElementById("formularioGestionarUsuario");

// Inputs del formulario
const inputCI = document.getElementById("ci");
const inputContrasenia = document.getElementById("contrasenia");
const entradaRol = document.getElementById("rol");

// Filtro de usuarios
const filtroPorRol = document.getElementById("filtroPorRol");
const btnLimpiarFiltroUsuarios = document.getElementById("btnLimpiarFiltroUsuarios");

// Variable para controlar el modo de edición

let modoEdicion = false;

const formularios = document.querySelectorAll(".formularioDesactivarUsuario");
const formulariosActivar = document.querySelectorAll(".formularioActivarUsuario");

function limpiarEstadoFormulario() {
    modoEdicion = false;
    inputCI.readonly = false;
    formulario.reset();
}

function abrirDialogCrear() {
    modoEdicion = false;
    inputCI.readonly = false;
    formulario.reset();
    dialog.showModal();
}

function cerrarDialog() {
    formulario.reset();
    inputCI.readonly = false;
    modoEdicion = false;
    dialog.close();
}

function confirmarDesactivarUsuario(eventoBorrar) {
    const confirmacion = confirm("¿Está seguro de que desea desactivar este usuario?");
    if (!confirmacion) {
        eventoBorrar.preventDefault();
    }
}

function confirmarActivarUsuario(eventoActivar) {
    const confirmacion = confirm("¿Está seguro de que desea activar este usuario?");
    if (!confirmacion) {
        eventoActivar.preventDefault();
    }
}

function editarUsuario(eventoEditar) {
    const btnEditar = eventoEditar.target.closest(".btnEditar");
    if (btnEditar === null) {
        return;
    }
    const fila = btnEditar.closest("tr");
    inputCI.value = fila.cells[0].textContent.trim();
    inputContrasenia.value = fila.cells[1].textContent.trim();
    entradaRol.value = fila.cells[2].textContent.trim();
    inputCI.readOnly = true;
    modoEdicion = true;
    dialog.showModal();
}

function gestion (eventoGestion) {
    if(modoEdicion === false) {
        formulario.action = "procesarAltaUsuario.php";
    }
    if(modoEdicion === true) {
        formulario.action = "procesarEditarUsuario.php";
    }

}

btnCrear.addEventListener("click", abrirDialogCrear);
btnCerrarDialog.addEventListener("click", cerrarDialog);
cuerpoTablaUsuarios.addEventListener("click", editarUsuario);
formulario.addEventListener("submit", gestion);

for (const formulario of formularios) {
    formulario.addEventListener("submit", confirmarDesactivarUsuario);
}
for (const formulario of formulariosActivar) {
    formulario.addEventListener("submit", confirmarActivarUsuario);
}
    function aplicarFiltroUsuarios() {
    const rolFiltro = filtroPorRol.value;
    const estadoFiltro = filtroPorEstado.value;
    cuerpoTablaUsuarios.replaceChildren();
    const usuarios = cargarUsuariosGuardadosLocal();
   const filtrados = usuarios.filter(u =>
    (rolFiltro === "" || (u.roles && u.roles.includes(rolFiltro))) &&
    (estadoFiltro === "" || u.activo === (estadoFiltro === "activo"))
);
        agregarFilaUsuario(usuario);
    }



