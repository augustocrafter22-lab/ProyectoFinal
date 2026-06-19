document.addEventListener("DOMContentLoaded", iniciarModificarDiagnostico);

function iniciarModificarDiagnostico() {
        cargarSelectDiagnosticos();

    const select = document.getElementById("modificarDiagnosticoSelect");
    select.addEventListener("change", cargarTextoDiagnostico);

        const formulario = document.getElementById("formModificarDiagnostico");
        formulario.addEventListener("submit", modificarDiagnostico);
}

function cargarSelectDiagnosticos() {
        const select = document.getElementById("modificarDiagnosticoSelect");
        const diagnosticos = obtenerDatos();

        select.replaceChildren();

    if (diagnosticos.length === 0) {
        const opcion = document.createElement("option");
        opcion.value = "";
        opcion.textContent = "No hay diagnósticos registrados";
        select.appendChild(opcion);
        return;
    }

        const opcionVacia = document.createElement("option");
        opcionVacia.value = "";
        opcionVacia.textContent = " Seleccione un diagnóstico ";
        select.appendChild(opcionVacia);

    diagnosticos.forEach(function (diagnostico) {
    const opcion = document.createElement("option");
    opcion.value = diagnostico.id;
    opcion.textContent = diagnostico.id + " | " + diagnostico.ticketId + " | " + diagnostico.fecha;
    select.appendChild(opcion);
    });
}

function cargarTextoDiagnostico() {
        const id = document.getElementById("modificarDiagnosticoSelect").value;
        const textarea = document.getElementById("modificarDiagnosticoTexto");

    if (!id) {
        textarea.value = "";
        return;
    }

        const diagnosticos = obtenerDatos();
        const diagnostico = diagnosticos.find(function (d) { return d.id === id; });

    if (diagnostico) {
        textarea.value = diagnostico.texto;
    }
}

function modificarDiagnostico(evento) {
        evento.preventDefault();

        const id = document.getElementById("modificarDiagnosticoSelect").value;
        const texto = document.getElementById("modificarDiagnosticoTexto").value.trim();

    if (!id) {
        mostrarMensaje("Seleccioná un diagnóstico.");
        return;
    }

    if (!validarMinimo(texto, 10)) {
        mostrarMensaje("El diagnóstico debe tener al menos 10 caracteres.");
        return;
    }

        const diagnosticos = obtenerDatos();
        const diagnostico = diagnosticos.find(function (d) { return d.id === id; });

    if (!diagnostico) {
        mostrarMensaje("No se encontró el diagnóstico seleccionado.");
        return;
    }

        diagnostico.texto = texto;

        guardarDatos(diagnosticos);
        cargarSelectDiagnosticos();

    document.getElementById("formModificarDiagnostico").reset();
    mostrarMensaje("Diagnóstico modificado correctamente.");
}

function obtenerDatos() {
    const datosGuardados = localStorage.getItem("diagnosticos");

        if (datosGuardados === null) {
    return [];
    }

        return JSON.parse(datosGuardados);
}

function guardarDatos(diagnosticos) {
        localStorage.setItem("diagnosticos", JSON.stringify(diagnosticos));
}

function validarMinimo(texto, minimo) {
    return texto.length >= minimo;
}

function mostrarMensaje(mensaje) {
    alert(mensaje);
}