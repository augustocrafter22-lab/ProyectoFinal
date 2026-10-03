const URL_API_USUARIOS = "api/usuarios.php";
 
// Textos ya traducidos por el servidor (ver el bloque JSON al final de administrador.php).
const textos = JSON.parse(document.getElementById("textosAdministrador").textContent);
const cedulaSesion = document.body.dataset.cedulaSesion;
const tokenCSRF = document.querySelector('meta[name="csrf-token"]').content;
 
// Mensajes de la página y del diálogo
const mensajeAdministrador = document.getElementById("mensajeAdministrador");
const mensajeDialogo = document.getElementById("mensajeDialogoUsuario");
 
// Tabla de usuarios
const cuerpoTablaUsuarios = document.getElementById("cuerpoTablaUsuarios");
 
// Dialog para gestionar usuarios
const dialog = document.getElementById("dialogGestionarUsuario");
const btnAbrirDialog = document.getElementById("btnCrear");
const btnCerrarDialog = document.getElementById("btnCerrarGestionarUsuario");
const btnGuardar = document.getElementById("btnGuardarUsuario");
 
// Formulario de gestión de usuarios
const formulario = document.getElementById("formularioGestionarUsuario");
const inputCI = document.getElementById("ci");
const inputNombre = document.getElementById("nombre");
const inputApellido = document.getElementById("apellido");
const inputContrasenia = document.getElementById("contrasenia");
const checkboxesRol = document.querySelectorAll("input[name='roles[]']");
 
// Cédula del usuario que se está editando; null cuando el diálogo está en modo alta.
let cedulaEnEdicion = null;
 
function mostrarMensaje(texto, esError) {
    mensajeAdministrador.textContent = texto;
    mensajeAdministrador.style.color = esError ? "red" : "green";
}
 
function mostrarMensajeDialogo(texto) {
    mensajeDialogo.textContent = texto;
    mensajeDialogo.style.color = "red";
}
 
/**
 * Llama a la API de usuarios y devuelve lo que viene bajo la clave "datos".
 * Si la API responde con error lanza un Error con el mensaje del servidor.
 */
async function llamarAPI(parametros = "", metodo = "GET", cuerpo = null) {
    const opciones = { method: metodo, headers: {} };
 
    // GET no modifica datos, por eso solo se envía el token en POST, PUT y DELETE.
    if (metodo !== "GET") {
        opciones.headers["X-CSRF-Token"] = tokenCSRF;
    }
    if (cuerpo !== null) {
        opciones.headers["Content-Type"] = "application/json";
        opciones.body = JSON.stringify(cuerpo);
    }
 
    let respuesta;
    try {
        respuesta = await fetch(URL_API_USUARIOS + parametros, opciones);
    } catch {
        throw new Error(textos.errorConexion);
    }
 
    let json;
    try {
        json = await respuesta.json();
    } catch {
        throw new Error(textos.errorConexion);
    }
 
    // La sesión venció: se vuelve al login en lugar de mostrar un error suelto.
    if (respuesta.status === 401) {
        window.location.href = "Login.php";
    }
 
    if (!respuesta.ok || !json.exito) {
        throw new Error(json.mensaje ?? textos.errorConexion);
    }
 
    return json.datos;
}
 
function parametroId(cedula) {
    return `?id=${encodeURIComponent(cedula)}`;
}
 
function crearBoton(texto, clase, alHacerClick) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.className = clase;
    boton.textContent = texto;
    boton.addEventListener("click", alHacerClick);
 
    return boton;
}
 
function crearCelda(texto) {
    const celda = document.createElement("td");
    celda.textContent = texto;
 
    return celda;
}
 
function crearFilaUsuario(usuario) {
    const fila = document.createElement("tr");
 
    const celdaRoles = usuario.roles.map(rol => textos.roles[rol] ?? rol).join(", ");
    const celdaEstado = usuario.activo ? textos.activo : textos.inactivo;
 
    const operaciones = document.createElement("div");
    operaciones.className = "Operaciones";
    operaciones.appendChild(crearBoton(textos.btnEditar, "btnEditar", () => abrirDialogEditar(usuario.cedula)));
 
    // El coordinador no puede desactivarse ni eliminarse a sí mismo (la API también lo rechaza).
    if (usuario.cedula !== cedulaSesion) {
        if (usuario.activo) {
            operaciones.appendChild(crearBoton(textos.btnDesactivar, "btnEliminar", () => cambiarEstado(usuario.cedula, false)));
        } else {
            operaciones.appendChild(crearBoton(textos.btnActivar, "btnActivar", () => cambiarEstado(usuario.cedula, true)));
        }
        operaciones.appendChild(crearBoton(textos.btnEliminar, "btnEliminar", () => eliminarUsuario(usuario.cedula)));
    }
 
    const celdaAcciones = document.createElement("td");
    celdaAcciones.appendChild(operaciones);
 
    fila.append(
        crearCelda(usuario.cedula),
        crearCelda(usuario.nombre),
        crearCelda(usuario.apellido),
        crearCelda(celdaRoles),
        crearCelda(celdaEstado),
        celdaAcciones
    );
 
    return fila;
}
 
function mostrarFilaInformativa(texto) {
    const fila = document.createElement("tr");
    const celda = crearCelda(texto);
    celda.colSpan = 6;
    fila.appendChild(celda);
    cuerpoTablaUsuarios.replaceChildren(fila);
}
 
// GET - Carga el listado completo y arma la tabla.
async function cargarUsuarios() {
    try {
        const usuarios = await llamarAPI();
 
        if (usuarios.length === 0) {
            mostrarFilaInformativa(textos.sinUsuarios);
            return;
        }
 
        cuerpoTablaUsuarios.replaceChildren(...usuarios.map(crearFilaUsuario));
    } catch (error) {
        cuerpoTablaUsuarios.replaceChildren();
        mostrarMensaje(error.message, true);
    }
}
 
function limpiarCheckboxesRol() {
    for (const checkbox of checkboxesRol) {
        checkbox.checked = false;
        checkbox.disabled = false;
    }
}
 
function abrirDialogCrear() {
    cedulaEnEdicion = null;
    formulario.reset();
    limpiarCheckboxesRol();
    mensajeDialogo.textContent = "";
    inputCI.readOnly = false;
    inputContrasenia.placeholder = textos.placeholderContrasenia;
    dialog.showModal();
}
 
// GET ?id= - Trae el usuario actualizado desde la API y abre el diálogo en modo edición.
async function abrirDialogEditar(cedula) {
    try {
        const usuario = await llamarAPI(parametroId(cedula));
 
        cedulaEnEdicion = usuario.cedula;
        formulario.reset();
        limpiarCheckboxesRol();
        mensajeDialogo.textContent = "";
 
        inputCI.value = usuario.cedula;
        inputCI.readOnly = true;
        inputNombre.value = usuario.nombre;
        inputApellido.value = usuario.apellido;
        inputContrasenia.value = "";
        inputContrasenia.placeholder = textos.placeholderContraseniaEditar;
 
        for (const checkbox of checkboxesRol) {
            checkbox.checked = usuario.roles.includes(checkbox.value);
 
            // El coordinador no puede quitarse su propio rol (la API también lo rechaza).
            if (usuario.cedula === cedulaSesion && checkbox.value === "coordinador") {
                checkbox.disabled = true;
            }
        }
 
        dialog.showModal();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}
 
// Al cerrarse el diálogo (botón, Esc o guardado) se deja todo limpio.
function restablecerDialog() {
    formulario.reset();
    limpiarCheckboxesRol();
    mensajeDialogo.textContent = "";
    inputCI.readOnly = false;
    cedulaEnEdicion = null;
}
 
// POST (alta) o PUT (edición) según el modo del diálogo.
async function guardarUsuario(eventoFormulario) {
    eventoFormulario.preventDefault();
 
    const roles = [...checkboxesRol].filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
    mensajeDialogo.textContent = "";
    btnGuardar.disabled = true;
 
    try {
        let mensajeExito;
 
        if (cedulaEnEdicion === null) {
            await llamarAPI("", "POST", {
                cedula: inputCI.value.trim(),
                nombre: inputNombre.value,
                apellido: inputApellido.value,
                contrasenia: inputContrasenia.value,
                roles
            });
            mensajeExito = textos.exitoCreado;
        } else {
            const cambios = { nombre: inputNombre.value, apellido: inputApellido.value, roles };
 
            // La contraseña solo se envía si se escribió una nueva.
            if (inputContrasenia.value.trim() !== "") {
                cambios.contrasenia = inputContrasenia.value;
            }
 
            await llamarAPI(parametroId(cedulaEnEdicion), "PUT", cambios);
            mensajeExito = textos.exitoActualizado;
        }
 
        dialog.close();
        mostrarMensaje(mensajeExito, false);
        await cargarUsuarios();
    } catch (error) {
        // El error se muestra dentro del diálogo para poder corregir sin perder lo escrito.
        mostrarMensajeDialogo(error.message);
    } finally {
        btnGuardar.disabled = false;
    }
}
 
// PUT {activo} - Activa o desactiva un usuario.
async function cambiarEstado(cedula, activo) {
    if (!confirm(activo ? textos.confirmarActivar : textos.confirmarDesactivar)) {
        return;
    }
 
    try {
        await llamarAPI(parametroId(cedula), "PUT", { activo });
        mostrarMensaje(activo ? textos.exitoActivado : textos.exitoDesactivado, false);
        await cargarUsuarios();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}
 
// DELETE - Elimina un usuario (la API rechaza si tiene registros asociados).
async function eliminarUsuario(cedula) {
    if (!confirm(textos.confirmarEliminar)) {
        return;
    }
 
    try {
        await llamarAPI(parametroId(cedula), "DELETE");
        mostrarMensaje(textos.exitoEliminado, false);
        await cargarUsuarios();
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}
 
btnAbrirDialog.addEventListener("click", abrirDialogCrear);
btnCerrarDialog.addEventListener("click", () => dialog.close());
dialog.addEventListener("close", restablecerDialog);
formulario.addEventListener("submit", guardarUsuario);
 
mostrarFilaInformativa(textos.cargando);
cargarUsuarios();