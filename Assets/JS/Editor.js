const btnAltaUsuario = document.getElementById("btnAltaUsuario");
const btnCerrarGestionarUsuario = document.getElementById("btnCerrarGestionarUsuario");
const dialogGestionarUsuario = document.getElementById("dialogGestionarUsuario");
const cuerpoTablaUsuarios = document.getElementById("cuerpoTablaUsuarios");
const formularioGestionarUsuario = document.getElementById("formularioGestionarUsuario");
const entradaCI = document.getElementById("ci");
const entradaContrasenia = document.getElementById("contrasenia");
const entradaRol = document.getElementById("rol");
const filtroPorRol = document.getElementById("filtroPorRol");
const btnLimpiarFiltroUsuarios = document.getElementById("btnLimpiarFiltroUsuarios");


let usuarioEnEdicion = false;


function limpiarEstadoGestionarUsuario() {
    usuarioEnEdicion = false;
    entradaCI.readOnly = false;
    formularioGestionarUsuario.reset();
}

function abrirAltaUsuario() {
    limpiarEstadoGestionarUsuario();
    dialogGestionarUsuario.showModal();
}

function cerrarGestionarUsuario() {
    limpiarEstadoGestionarUsuario();
    dialogGestionarUsuario.close();
}

function abrirModificarUsuario(ci) {
    usuarioEnEdicion = true;

    const usuarios = cargarUsuariosGuardadosLocal();
    const usuarioAModificar = usuarios.find(usuario => usuario.ci === ci);

    if (usuarioAModificar === undefined) return;

    entradaCI.value = usuarioAModificar.ci;
    entradaContrasenia.value = usuarioAModificar.contrasenia;
    entradaRol.value = usuarioAModificar.rol;

    entradaCI.readOnly = true;
    entradaRol.disabled = usuarioAModificar.ci === "11111111";

    dialogGestionarUsuario.showModal();
}


function cargarUsuariosGuardadosLocal() {
    const usuariosGuardados = localStorage.getItem("usuarios");
    if (usuariosGuardados === null) return [];
    return JSON.parse(usuariosGuardados);
}

function obtenerDatosFormularioUsuario() {
    const usuario = {
        ci: entradaCI.value.trim(),
        contrasenia: entradaContrasenia.value.trim(),
        rol: entradaRol.value,
    };
    return usuario;
}

function agregarFilaUsuario(usuario) {
    const fila = document.createElement("tr");

    const campoCI = document.createElement("td");
    campoCI.textContent = usuario.ci;

    const campoRol = document.createElement("td");
    campoRol.textContent = usuario.rol;

    const campoOperaciones = document.createElement("td");
    const cajaOperaciones = document.createElement("div");
    cajaOperaciones.classList.add("cajaOperaciones");

    const btnModificar = document.createElement("button");
    btnModificar.type = "button";
    btnModificar.textContent = "Modificar";
    btnModificar.classList.add("btnOperacion");
    btnModificar.addEventListener("click", () => abrirModificarUsuario(usuario.ci));

    const btnEliminar = document.createElement("button");
    btnEliminar.type = "button";
    btnEliminar.textContent = "Eliminar";
    btnEliminar.classList.add("btnOperacion");
    btnEliminar.addEventListener("click", () => eliminarUsuarioLocal(usuario.ci));


    cajaOperaciones.appendChild(btnModificar);
    cajaOperaciones.appendChild(btnEliminar);
    campoOperaciones.appendChild(cajaOperaciones);

    fila.appendChild(campoCI);
    fila.appendChild(campoRol);
    fila.appendChild(campoOperaciones);

    cuerpoTablaUsuarios.appendChild(fila);
}

function actualizarTabla() {
    cuerpoTablaUsuarios.replaceChildren();
    const usuarios = cargarUsuariosGuardadosLocal();
    for (const usuario of usuarios) {
        agregarFilaUsuario(usuario);
    }
}


function actualizarUsuariosLocal(usuarios) {
    localStorage.setItem("usuarios", JSON.stringify(usuarios));
}

function guardarUsuarioLocal(usuario) {
    const usuarios = cargarUsuariosGuardadosLocal();
    const ciExistente = usuarios.some(u => u.ci === usuario.ci);

    if (ciExistente) {
        alert("Ya existe un usuario con esa CI.");
        return;
    }

    usuarios.push(usuario);
    actualizarUsuariosLocal(usuarios);
}

function modificarUsuarioLocal(usuarioEnFormulario) {
    const usuarios = cargarUsuariosGuardadosLocal();
    const usuarioAModificar = usuarios.find(u => u.ci === usuarioEnFormulario.ci);

    if (usuarioAModificar === undefined) return;

    usuarioAModificar.contrasenia = usuarioEnFormulario.contrasenia;
    usuarioAModificar.rol = usuarioEnFormulario.rol;

    actualizarUsuariosLocal(usuarios);
}

function eliminarUsuarioLocal(ci) {
    if (ci === "11111111") {
        alert("No se puede eliminar el usuario raíz.");
        return;
    }

    const usuarios = cargarUsuariosGuardadosLocal();
    const usuariosActualizados = usuarios.filter(u => u.ci !== ci);
    actualizarUsuariosLocal(usuariosActualizados);
    actualizarTabla();
}

function gestionarUsuario(eventoFormulario) {
    eventoFormulario.preventDefault();

    const usuario = obtenerDatosFormularioUsuario();

    if (!usuarioEnEdicion) {
        guardarUsuarioLocal(usuario);
    } else {
        modificarUsuarioLocal(usuario);
    }

    cerrarGestionarUsuario();
    actualizarTabla();
}
function aplicarFiltroUsuarios() {
    const rolFiltro = filtroPorRol.value;
    cuerpoTablaUsuarios.replaceChildren();
    const usuarios = cargarUsuariosGuardadosLocal();
    const filtrados = usuarios.filter(u => rolFiltro === "" || u.rol === rolFiltro);
    for (const usuario of filtrados) {
        agregarFilaUsuario(usuario);
    }
}

filtroPorRol.addEventListener("change", aplicarFiltroUsuarios);

btnLimpiarFiltroUsuarios.addEventListener("click", function() {
    filtroPorRol.value = "";
    actualizarTabla();
});

formularioGestionarUsuario.addEventListener("submit", gestionarUsuario);
btnAltaUsuario.addEventListener("click", abrirAltaUsuario);
btnCerrarGestionarUsuario.addEventListener("click", cerrarGestionarUsuario);
dialogGestionarUsuario.addEventListener("cancel", limpiarEstadoGestionarUsuario);

actualizarTabla();