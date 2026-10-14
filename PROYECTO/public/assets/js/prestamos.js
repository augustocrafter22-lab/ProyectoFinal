const formularioPrestamo = document.getElementById("formularioPrestamo");
const cuerpoTablaPrestamos = document.getElementById("cuerpoTablaPrestamos");
const mensajePrestamos = document.getElementById("mensajePrestamos");
const filtroEstadoPrestamo = document.getElementById("filtroEstadoPrestamo");

const campoIdEquipo = document.getElementById("idEquipo");
const campoCedula = document.getElementById("cedulaSolicitante");
const campoFechaEstimada = document.getElementById("fechaDevolucionEstimada");

/**
 * OPERACIONES CON LA API
 */

//GET - Obtiene los préstamos, opcionalmente solo los de un estado
async function obtenerPrestamos(estado) {
    const { datos } = await llamarApi("prestamos", { parametros: { estado: estado } });

    return datos;
}

//GET - Obtiene solo los equipos que están disponibles para prestar
async function obtenerEquiposDisponibles() {
    const { datos } = await llamarApi("equipos", { parametros: { disponibilidad: "Disponible" } });

    return datos;
}

//POST - Registra un préstamo nuevo
async function registrarPrestamo(eventoFormulario) {
    eventoFormulario.preventDefault();

    const datosPrestamo = {
        idEquipo: campoIdEquipo.value,
        cedulaSolicitante: campoCedula.value.trim(),
        fechaDevolucionEstimada: campoFechaEstimada.value
    };

    try {
        const { mensaje } = await llamarApi("prestamos", { metodo: "POST", cuerpo: datosPrestamo });

        mostrarMensaje(mensajePrestamos, mensaje, false);
        formularioPrestamo.reset();
        await cargarPrestamos();
    } catch (error) {
        mostrarMensaje(mensajePrestamos, error.message, true);
    }

    //Se recarga siempre: si el equipo ya no estaba disponible, deja de aparecer en la lista
    await cargarEquiposDisponibles();
}

//PUT - Registra la devolución del equipo de un préstamo activo
async function devolverPrestamo(idPrestamo) {
    try {
        const { mensaje } = await llamarApi("prestamos", {
            metodo: "PUT",
            parametros: { id: idPrestamo },
            cuerpo: { estado: "Devuelto" }
        });

        mostrarMensaje(mensajePrestamos, mensaje, false);
    } catch (error) {
        mostrarMensaje(mensajePrestamos, error.message, true);
    }

    await cargarPrestamos();
    await cargarEquiposDisponibles();
}

//DELETE - Elimina un préstamo que ya fue devuelto
async function eliminarPrestamo(idPrestamo) {
    try {
        const { mensaje } = await llamarApi("prestamos", { metodo: "DELETE", parametros: { id: idPrestamo } });

        mostrarMensaje(mensajePrestamos, mensaje, false);
        await cargarPrestamos();
    } catch (error) {
        mostrarMensaje(mensajePrestamos, error.message, true);
    }
}

/**
 * GESTIÓN DE FILAS DE LA TABLA
 */

function crearFilaPrestamo(prestamo) {
    const fila = document.createElement("tr");

    const esActivo = prestamo.estado === "Activo";

    //Un préstamo activo se puede devolver, uno devuelto se puede eliminar
    const celdaAcciones = document.createElement("td");
    if (esActivo) {
        celdaAcciones.appendChild(
            crearBoton(traducir("prestamos.btnDevolver"), "btnAccion", () => devolverPrestamo(prestamo.idPrestamo))
        );
    } else {
        celdaAcciones.appendChild(
            crearBoton(traducir("prestamos.btnEliminar"), "btnAccion btnPeligro", () => eliminarPrestamo(prestamo.idPrestamo))
        );
    }

    fila.append(
        crearCelda(prestamo.idPrestamo),
        crearCelda(prestamo.idEquipo),
        crearCelda(`${prestamo.cedulaSolicitante} - ${prestamo.solicitante}`),
        crearCelda(prestamo.fechaPrestamo.slice(0, 16)),
        //La devolución estimada se guarda hasta el final del día, por eso solo se muestra la fecha
        crearCelda(prestamo.fechaDevolucionEstimada.slice(0, 10)),
        crearCelda(prestamo.fechaDevolucionReal ? prestamo.fechaDevolucionReal.slice(0, 16) : "-"),
        crearCelda(esActivo ? traducir("prestamos.estadoActivo") : traducir("prestamos.estadoDevuelto")),
        celdaAcciones
    );

    return fila;
}

async function cargarPrestamos() {
    try {
        const prestamos = await obtenerPrestamos(filtroEstadoPrestamo.value);

        cuerpoTablaPrestamos.replaceChildren();
        for (const prestamo of prestamos) {
            cuerpoTablaPrestamos.appendChild(crearFilaPrestamo(prestamo));
        }
    } catch (error) {
        mostrarMensaje(mensajePrestamos, error.message, true);
    }
}

//Vuelve a llenar la lista de equipos, que cambia con cada préstamo o devolución
async function cargarEquiposDisponibles() {
    try {
        const equipos = await obtenerEquiposDisponibles();

        vaciarOpciones(campoIdEquipo);
        agregarOpciones(
            campoIdEquipo,
            equipos,
            (equipo) => equipo.idEquipo,
            (equipo) => `${equipo.idEquipo} - ${equipo.marca} (${equipo.laboratorio})`
        );

        if (equipos.length === 0) {
            mostrarMensaje(mensajePrestamos, traducir("prestamos.sinEquiposDisponibles"), true);
        }
    } catch (error) {
        mostrarMensaje(mensajePrestamos, error.message, true);
    }
}

formularioPrestamo.addEventListener("submit", registrarPrestamo);
filtroEstadoPrestamo.addEventListener("change", cargarPrestamos);

async function iniciar() {
    await iniciarPagina();

    //No se puede elegir una fecha de devolución anterior a hoy
    campoFechaEstimada.min = obtenerFechaHoy();

    await cargarPrestamos();
    await cargarEquiposDisponibles();
}

iniciar();
