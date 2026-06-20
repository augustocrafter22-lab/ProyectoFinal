const form = document.getElementById("LabForm");

form.addEventListener('submit', function(e) {
    e.preventDefault();

    const solicitud = {
        id: Date.now(),
        laboratorio: document.getElementById("laboratorioSolicitud").value,
    }
    const solicitudGuardada = JSON.parse(localStorage.getItem('Solicitudes')) || [];

    solicitudGuardada.push(solicitud);

    localStorage.setItem("Solicitudes", JSON.stringify(solicitudGuardada));
    console.log("Ticket guardado:", solicitud);
    form.reset();
});