const dialog = document.getElementById("dialogGestionarUsuario");
const btnAbrirDialog = document.getElementById("btnAltaUsuario");
const btnCerrarDialog = document.getElementById("btnCerrarGestionarUsuario");
const formulario = document.getElementById("formularioGestionarUsuario");
const inputCI = document.getElementById("ci");

let modoEdicion = false;

// Abrir dialog para agregar
btnAbrirDialog.addEventListener("click", () => {
    modoEdicion = false;
    formulario.reset();
    formulario.action = "../controlador/procesarAltaUsuario.php";
    formulario.method = "POST";
    inputCI.disabled = false;
    inputCI.readOnly = false;
    document.getElementById("contrasenia").required = true;
    document.getElementById("rol").required = true;
    document.getElementById("ci").required = true;
    dialog.showModal();
});

// Cerrar dialog
btnCerrarDialog.addEventListener("click", () => {
    dialog.close();
});

// Editar usuario
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btnEditar")) {
        const cedula = e.target.dataset.cedula;
        const fila = e.target.closest("tr");
        const rol = fila.cells[1].textContent.trim().toLowerCase();

        modoEdicion = true;

        document.getElementById("ci").value = cedula;
        document.getElementById("ci").readOnly = true;  // ← Usa readOnly en lugar de disabled
        document.getElementById("ci").required = false;
        
        document.getElementById("contrasenia").value = "";
        document.getElementById("contrasenia").required = false;
        document.getElementById("contrasenia").placeholder = "Dejar en blanco para no cambiar";
        
        document.getElementById("rol").value = rol;
        document.getElementById("rol").required = true;

        formulario.action = "../controlador/procesarEditarUsuario.php";
        formulario.method = "POST";
        dialog.showModal();
    }
});

// Eliminar usuario
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btnEliminar")) {
        const cedula = e.target.dataset.cedula;

        if (confirm("¿Seguro que desea eliminar este usuario?")) {
            const formEliminar = document.createElement("form");
            formEliminar.method = "POST";
            formEliminar.action = "../controlador/procesarEliminarUsuario.php";

            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "cedula";
            input.value = cedula;

            formEliminar.appendChild(input);
            document.body.appendChild(formEliminar);
            formEliminar.submit();
        }
    }
});