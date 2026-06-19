document.addEventListener("DOMContentLoaded", iniciarCambiarEstado);

function iniciarCambiarEstado() {
        const selectEquipo = document.getElementById("cambiarEstadoEquipoSelect");
        selectEquipo.addEventListener("change", cargarEstadoActual);

        const formulario = document.getElementById("formCambiarEstado");
        formulario.addEventListener("submit", registrarCambioEstado);
}

function cargarEstadoActual() {
        const equipoId = document.getElementById("cambiarEstadoEquipoSelect").value;
        const inputEstadoActual = document.getElementById("cambiarEstadoActual");

    if (!equipoId) {
        inputEstadoActual.value = "";
        return;
}

        const equipos = obtenerEquipos();
        const equipo = equipos.find(function (e) { return e.id === equipoId; });

    if (equipo) {
        inputEstadoActual.value = equipo.estado;
} else {
        inputEstadoActual.value = "Sin estado registrado";
}
}

function registrarCambioEstado(evento) {
        evento.preventDefault();

        const equipoId = document.getElementById("cambiarEstadoEquipoSelect").value;
        const estadoNuevo = document.getElementById("cambiarEstadoNuevo").value;

    if (!equipoId) {
        mostrarMensaje("Seleccioná un equipo.");
        return;
}

    if (!estadoNuevo) {
        mostrarMensaje("Seleccioná el nuevo estado.");
        return;
}

        const equipos = obtenerEquipos();
        const equipo = equipos.find(function (e) { return e.id === equipoId; });

        const estadoAnterior = equipo ? equipo.estado : "Sin estado";

    if (estadoAnterior === estadoNuevo) {
        mostrarMensaje("El equipo ya se encuentra en ese estado.");
        return;
}


        const historial = obtenerHistorialEstados();

        const nuevoRegistro = {
    id: crearId("EST"),
    equipoId: equipoId,
    estadoAnterior: estadoAnterior,
    estadoNuevo: estadoNuevo,
    fecha: obtenerFechaActual(),
    usuario: localStorage.getItem("CI")
};

        historial.push(nuevoRegistro);
        guardarHistorialEstados(historial);


    if (equipo) {
        equipo.estado = estadoNuevo;
} else {
        equipos.push({ id: equipoId, estado: estadoNuevo, ubicacion: "-" });
}
        guardarEquipos(equipos);

        document.getElementById("formCambiarEstado").reset();
        document.getElementById("cambiarEstadoActual").value = "";
        mostrarMensaje("Estado del equipo actualizado correctamente.");
}

    function obtenerEquipos() {
        const datos = localStorage.getItem("equipos");
        return datos === null ? [] : JSON.parse(datos);
}

    function guardarEquipos(equipos) {
        localStorage.setItem("equipos", JSON.stringify(equipos));
}

    function obtenerHistorialEstados() {
        const datos = localStorage.getItem("historialEstados");
        return datos === null ? [] : JSON.parse(datos);
}

    function guardarHistorialEstados(historial) {
        localStorage.setItem("historialEstados", JSON.stringify(historial));
}

    function crearId(prefijo) {
        return prefijo + "-" + Date.now();
}

    function obtenerFechaActual() {
        return new Date().toLocaleDateString("es-UY");
}

    function mostrarMensaje(mensaje) {
        alert(mensaje);
}