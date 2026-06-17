const fechaActual = new Date().toLocaleDateString();

const ticket = {
    equipo: document.getElementById("equipo").value,
    laboratorio: document.getElementById("laboratorioTaller").value,
    asunto: document.getElementById("asunto").value,
    descripcion: document.getElementById("descripcion").value,
    prioridad: document.getElementById("prioridad").value,
    fecha: fechaActual,
    turno: document.getElementById("turno").value,
    grupo: document.getElementById("grupo").value,
    profesor: document.getElementById("profesor").value
};