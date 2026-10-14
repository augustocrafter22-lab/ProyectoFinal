const mensajeAdministrador = document.getElementById("mensajeAdministrador");
const cuerpoTablaUsuarios = document.getElementById("cuerpoTablaUsuarios");

//Constantes para el cuadro de diálogo
const btnAltaUsuario = document.getElementById("btnAltaUsuario");
const btnCerrarGestionarUsuario = document.getElementById("btnCerrarGestionarUsuario");
const dialogGestionarUsuario = document.getElementById("dialogGestionarUsuario");

//Constante para manipular el formulario y sus campos
const formularioGestionarUsuario = document.getElementById("formularioGestionarUsuario");
const campoCedula = document.getElementById("cedula");
const campoNombre = document.getElementById("nombre");
const campoApellido = document.getElementById("apellido");
const campoClave = document.getElementById("clave");
const casillasRoles = document.querySelectorAll("input[name='roles']");

//Auxiliar para saber si se está agregando o modificando un usuario
let usuarioEnEdicion = false;

/**
 * GESTIÓN DEL ESTADO DEL FORMULARIO / MODAL
 */

//Limpia todos los campos y configuraciones seleccionadas del formulario
function limpiarFormularioUsuario() {
    usuarioEnEdicion = false;
    campoCedula.readOnly = false;
    formularioGestionarUsuario.reset();
}

function abrirAltaUsuario() {
    limpiarFormularioUsuario();

    dialogGestionarUsuario.showModal();
}

//Carga en el formulario los datos del usuario elegido y abre el modal
function abrirEdicionUsuario(usuario) {
    limpiarFormularioUsuario();
    usuarioEnEdicion = true;

    campoCedula.value = usuario.cedula;
    campoCedula.readOnly = true;
    campoNombre.value = usuario.nombre;
    campoApellido.value = usuario.apellido;

    for (const casilla of casillasRoles) {
        casilla.checked = usuario.roles.includes(casilla.value);
    }

    dialogGestionarUsuario.showModal();
}

function cerrarGestionarUsuario() {
    limpiarFormularioUsuario();

    dialogGestionarUsuario.close();
}

/**
 * OBTENCIÓN DE DATOS DEL FORMULARIO
 */

//Captura los datos ingresados en el formulario. Al modificar solo se envía lo que el usuario escribió.
function obtenerDatosFormularioUsuario() {
    const roles = [...casillasRoles].filter((casilla) => casilla.checked).map((casilla) => casilla.value);
    const usuario = { cedula: campoCedula.value.trim(), roles: roles };

    if (!usuarioEnEdicion || campoNombre.value.trim() !== "") {
        usuario.nombre = campoNombre.value.trim();
    }

    if (!usuarioEnEdicion || campoApellido.value.trim() !== "") {
        usuario.apellido = campoApellido.value.trim();
    }

    if (!usuarioEnEdicion || campoClave.value !== "") {
        usuario.clave = campoClave.value;
    }

    return usuario;
}

/**
 * OPERACIONES CON LA API
 */

//GET - Obtiene todos los usuarios
async function obtenerUsuarios() {
    const { datos } = await llamarApi("usuarios");

    return datos;
}

//POST - Envía un nuevo usuario a la API
async function altaUsuario(usuario) {
    await llamarApi("usuarios", { metodo: "POST", cuerpo: usuario });
}

//PUT - Modifica los datos de un usuario existente (también activa y desactiva)
async function modificarUsuario(cambios) {
    await llamarApi("usuarios", { metodo: "PUT", cuerpo: cambios });
}

/**
 * GESTIÓN DE FILAS DE LA TABLA
 */

function crearFilaUsuario(usuario) {
    const fila = document.createElement("tr");

    const roles = usuario.roles.map((rol) => traducir(`comun.${rol}`)).join(", ");
    const estado = usuario.activo ? traducir("administrador.activo") : traducir("administrador.inactivo");

    const cajaOperaciones = document.createElement("div");
    cajaOperaciones.className = "cajaOperaciones";
    cajaOperaciones.appendChild(
        crearBoton(traducir("administrador.btnEditar"), "btnAccion", () => abrirEdicionUsuario(usuario))
    );

    if (usuario.activo) {
        cajaOperaciones.appendChild(
            crearBoton(traducir("administrador.btnDesactivar"), "btnAccion btnPeligro", () => cambiarEstadoUsuario(usuario, false))
        );
    } else {
        cajaOperaciones.appendChild(
            crearBoton(traducir("administrador.btnActivar"), "btnAccion", () => cambiarEstadoUsuario(usuario, true))
        );
    }

    const celdaOperaciones = document.createElement("td");
    celdaOperaciones.appendChild(cajaOperaciones);

    fila.append(
        crearCelda(usuario.cedula),
        crearCelda(usuario.nombre),
        crearCelda(usuario.apellido),
        crearCelda(roles),
        crearCelda(estado),
        celdaOperaciones
    );

    return fila;
}

//Solicita los usuarios nuevamente a la API y genera las filas
async function actualizarTabla() {
    try {
        const usuarios = await obtenerUsuarios();

        cuerpoTablaUsuarios.replaceChildren();
        for (const usuario of usuarios) {
            cuerpoTablaUsuarios.appendChild(crearFilaUsuario(usuario));
        }
    } catch (error) {
        mostrarMensaje(mensajeAdministrador, error.message, true);
    }
}

/**
 * Decide si debe realizarse un POST o un PUT dependiendo del estado del formulario.
 */
async function gestionarUsuario(eventoFormulario) {
    eventoFormulario.preventDefault();

    const botonGuardar = formularioGestionarUsuario.querySelector("button[type='submit']");
    botonGuardar.disabled = true;

    try {
        const usuario = obtenerDatosFormularioUsuario();
        const claveMensaje = usuarioEnEdicion ? "administrador.usuarioActualizado" : "administrador.usuarioRegistrado";

        if (usuarioEnEdicion) {
            await modificarUsuario(usuario);
        } else {
            await altaUsuario(usuario);
        }

        cerrarGestionarUsuario();
        mostrarMensaje(mensajeAdministrador, traducir(claveMensaje), false);
        await actualizarTabla();
    } catch (error) {
        mostrarMensaje(mensajeAdministrador, error.message, true);
        cerrarGestionarUsuario();
    } finally {
        botonGuardar.disabled = false;
    }
}

async function cambiarEstadoUsuario(usuario, activo) {
    const claveConfirmacion = activo ? "administrador.confirmarActivar" : "administrador.confirmarDesactivar";

    if (!window.confirm(traducir(claveConfirmacion))) {
        return;
    }

    try {
        await modificarUsuario({ cedula: usuario.cedula, activo: activo });

        const claveMensaje = activo ? "administrador.usuarioActivado" : "administrador.usuarioDesactivado";
        mostrarMensaje(mensajeAdministrador, traducir(claveMensaje), false);
        await actualizarTabla();
    } catch (error) {
        mostrarMensaje(mensajeAdministrador, error.message, true);
    }
}

/**
 * EVENTOS
 */

formularioGestionarUsuario.addEventListener("submit", gestionarUsuario);
btnAltaUsuario.addEventListener("click", abrirAltaUsuario);
btnCerrarGestionarUsuario.addEventListener("click", cerrarGestionarUsuario);

//Al presionar Escape se limpia el estado del formulario
dialogGestionarUsuario.addEventListener("cancel", limpiarFormularioUsuario);

async function iniciar() {
    await iniciarPagina();
    await actualizarTabla();
}

iniciar();
