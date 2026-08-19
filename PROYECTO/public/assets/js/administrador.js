// Dialog para gestionar usuarios
const dialog = document.querySelector(".dialogGestionarUsuario");
const btnAbrirDialog = document.getElementById("btnCrear");
const btnCerrarDialog = document.getElementById("btnCerrarGestionarUsuario");

// Tabla de usuarios
const cuerpoTablaUsuarios = document.getElementById("cuerpoTablaUsuarios");

// Formulario de gestión de usuarios
const formulario = document.getElementById("formularioGestionarUsuario");

// Inputs del formulario
const inputCI = document.getElementById("ci");
const inputNombre = document.getElementById("nombre");
const inputApellido = document.getElementById("apellido");
const inputContrasenia = document.getElementById("contrasenia");
const checkboxesRol = document.querySelectorAll("input[name='roles[]']");

// Variable para controlar el modo de edición
let modoEdicion = false;

const formulariosDesactivar = document.querySelectorAll(".formularioDesactivarUsuario");
const formulariosActivar = document.querySelectorAll(".formularioActivarUsuario");

function limpiarCheckboxesRol() {
    for (const checkbox of checkboxesRol) {
        checkbox.checked = false;
    }
}

function abrirDialogCrear() {
    modoEdicion = false;
    inputCI.readOnly = false;
    formulario.reset();
    limpiarCheckboxesRol();
    dialog.showModal();
}

function cerrarDialog() {
    formulario.reset();
    limpiarCheckboxesRol();
    inputCI.readOnly = false;
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

    const cedula = fila.cells[0].textContent.trim();
    const nombre = fila.cells[1].textContent.trim();
    const apellido = fila.cells[2].textContent.trim();
    const rolesTexto = fila.cells[3].textContent.trim();
    const roles = rolesTexto === "" ? [] : rolesTexto.split(",").map(r => r.trim());

    formulario.reset();
    limpiarCheckboxesRol();

    inputCI.value = cedula;
    inputNombre.value = nombre;
    inputApellido.value = apellido;
    inputContrasenia.value = "";

    for (const checkbox of checkboxesRol) {
        checkbox.checked = roles.includes(checkbox.value);
    }

    inputCI.readOnly = true;
    modoEdicion = true;
    dialog.showModal();
}

function gestion(eventoGestion) {
    if (modoEdicion === false) {
        formulario.action = "procesarAltaUsuario.php";
    } else {
        formulario.action = "procesarEditarUsuario.php";
    }
}

btnAbrirDialog.addEventListener("click", abrirDialogCrear);
btnCerrarDialog.addEventListener("click", cerrarDialog);
cuerpoTablaUsuarios.addEventListener("click", editarUsuario);
formulario.addEventListener("submit", gestion);

for (const formulario of formulariosDesactivar) {
    formulario.addEventListener("submit", confirmarDesactivarUsuario);
}
for (const formulario of formulariosActivar) {
    formulario.addEventListener("submit", confirmarActivarUsuario);
}

