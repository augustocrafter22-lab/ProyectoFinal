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
const urlApiUsuarios = document.querySelector('meta[name="usuarios-api"]').content;
const tokenCSRF = document.querySelector('meta[name="csrf-token"]').content;
const mensajes = JSON.parse(document.querySelector('meta[name="administrador-mensajes"]').content);

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

function redirigirConMensaje(tipo, mensaje) {
    const url = new URL(window.location.href);
    url.searchParams.delete("exito");
    url.searchParams.delete("error");
    url.searchParams.set(tipo, mensaje);
    window.location.assign(url);
}

async function enviarSolicitudUsuario(metodo, datos) {
    const respuesta = await fetch(urlApiUsuarios, {
        method: metodo,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": tokenCSRF,
        },
        body: JSON.stringify(datos),
    });

    const cuerpo = await respuesta.json().catch(() => null);

    if (!cuerpo || typeof cuerpo.status !== "string" || typeof cuerpo.message !== "string") {
        throw new Error(`${mensajes.errorRespuestaApi} (HTTP ${respuesta.status}).`);
    }

    if (!respuesta.ok || cuerpo.status !== "success") {
        throw new Error(cuerpo.message);
    }

    return cuerpo.message;
}

async function gestionarUsuario(eventoGestion) {
    eventoGestion.preventDefault();

    const datos = {
        cedula: inputCI.value.trim(),
        roles: Array.from(checkboxesRol)
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value),
    };

    if (!modoEdicion || inputNombre.value.trim() !== "") {
        datos.nombre = inputNombre.value.trim();
    }
    if (!modoEdicion || inputApellido.value.trim() !== "") {
        datos.apellido = inputApellido.value.trim();
    }
    if (!modoEdicion || inputContrasenia.value !== "") {
        datos.clave = inputContrasenia.value;
    }

    const botonGuardar = formulario.querySelector('button[type="submit"]');
    botonGuardar.disabled = true;

    try {
        await enviarSolicitudUsuario(modoEdicion ? "PUT" : "POST", datos);
        const mensaje = modoEdicion ? mensajes.usuarioActualizado : mensajes.usuarioRegistrado;
        redirigirConMensaje("exito", mensaje);
    } catch (error) {
        redirigirConMensaje("error", error.message || mensajes.errorConexion);
    } finally {
        botonGuardar.disabled = false;
    }
}

async function cambiarEstadoUsuario(evento, activo) {
    evento.preventDefault();

    const mensajeConfirmacion = activo ? mensajes.confirmarActivar : mensajes.confirmarDesactivar;
    if (!confirm(mensajeConfirmacion)) {
        return;
    }

    const formularioEstado = evento.currentTarget;
    const boton = formularioEstado.querySelector('button[type="submit"]');
    const cedula = formularioEstado.querySelector('input[name="cedula"]').value;
    boton.disabled = true;

    try {
        await enviarSolicitudUsuario("PUT", { cedula, activo });
        const mensaje = activo ? mensajes.usuarioActivado : mensajes.usuarioDesactivado;
        redirigirConMensaje("exito", mensaje);
    } catch (error) {
        redirigirConMensaje("error", error.message || mensajes.errorConexion);
    } finally {
        boton.disabled = false;
    }
}

btnAbrirDialog.addEventListener("click", abrirDialogCrear);
btnCerrarDialog.addEventListener("click", cerrarDialog);
cuerpoTablaUsuarios.addEventListener("click", editarUsuario);
formulario.addEventListener("submit", gestionarUsuario);

for (const formulario of formulariosDesactivar) {
    formulario.addEventListener("submit", (evento) => cambiarEstadoUsuario(evento, false));
}
for (const formulario of formulariosActivar) {
    formulario.addEventListener("submit", (evento) => cambiarEstadoUsuario(evento, true));
}
