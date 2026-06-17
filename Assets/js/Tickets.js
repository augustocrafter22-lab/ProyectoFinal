const fechaActual = new Date().toLocaleDateString();

const ticket = {
    equipo: document.getElementById("equipo").value,
    laboratorio: document.getElementById("laboratorioTaller").value,
    asunto: document.getElementById("asunto").value,
    descripcion: document.getElementById("descripcion").value,
    fecha: fechaActual
};