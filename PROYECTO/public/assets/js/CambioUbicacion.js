document.addEventListener("DOMContentLoaded", iniciarCambiarUbicacion);

function iniciarCambiarUbicacion() {
    const selectEquipo = document.getElementById("cambiarUbicacionEquipoSelect");
    selectEquipo.addEventListener("change", cargarUbicacionActual);

    const formulario = document.getElementById("formCambiarUbicacion");
    formulario.addEventListener("submit", registrarCambioUbicacion);
}

function cargarUbicacionActual() {
    const equipoId = document.getElementById("cambiarUbicacionEquipoSelect").value;
    const inputUbicacionActual = document.getElementById("cambiarUbicacionActual");

    if (!equipoId) {
        inputUbicacionActual.value = "";
        return;
    }

    const equipos = obtenerEquipos();
    const equipo = equipos.find(function (e) {
        return e.id === equipoId;
    });

    if (equipo) {
        inputUbicacionActual.value = equipo.ubicacion;
    } else {
        inputUbicacionActual.value = "Sin ubicación registrada";
    }
}

function registrarCambioUbicacion(evento) {
    evento.preventDefault();

    const equipoId = document.getElementById("cambiarUbicacionEquipoSelect").value;
    const ubicacionNueva = document.getElementById("cambiarUbicacionNueva").value;

    if (!equipoId) {
        mostrarMensaje("Seleccioná un equipo.");
        return;
    }

    if (!ubicacionNueva) {
        mostrarMensaje("Seleccioná la nueva ubicación.");
        return;
    }

    const equipos = obtenerEquipos();
    const equipo = equipos.find(function (e) {
        return e.id === equipoId;
    });

    const ubicacionAnterior = equipo ? equipo.ubicacion : "Sin ubicación";

    if (ubicacionAnterior === ubicacionNueva) {
        mostrarMensaje("El equipo ya se encuentra en esa ubicación.");
        return;
    }

    const historial = obtenerHistorialUbicaciones();

    const nuevoRegistro = {
        id: crearId("UBI"),
        equipoId: equipoId,
        ubicacionAnterior: ubicacionAnterior,
        ubicacionNueva: ubicacionNueva,
        fecha: obtenerFechaActual(),
        usuario: localStorage.getItem("CI")
    };

    historial.push(nuevoRegistro);
    guardarHistorialUbicaciones(historial);

    if (equipo) {
        equipo.ubicacion = ubicacionNueva;
    } else {
        equipos.push({ id: equipoId, estado: "Disponible", ubicacion: ubicacionNueva });
    }
    guardarEquipos(equipos);

    document.getElementById("formCambiarUbicacion").reset();
    document.getElementById("cambiarUbicacionActual").value = "";
    mostrarMensaje("Ubicación del equipo actualizada correctamente.");
}

function obtenerEquipos() {
    const datos = localStorage.getItem("equipos");
    return datos === null ? [] : JSON.parse(datos);
}

function guardarEquipos(equipos) {
    localStorage.setItem("equipos", JSON.stringify(equipos));
}

function obtenerHistorialUbicaciones() {
    const datos = localStorage.getItem("historialUbicaciones");
    return datos === null ? [] : JSON.parse(datos);
}

function guardarHistorialUbicaciones(historial) {
    localStorage.setItem("historialUbicaciones", JSON.stringify(historial));
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