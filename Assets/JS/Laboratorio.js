const form = document.getElementById("LabForm");
const solicitudSoftware = document.getElementById("SolicitudDeSoftware");
const detalleSoftware = document.getElementById("DetalleSoftware");


form.addEventListener('submit', function(e) {
    e.preventDefault();

    const solicitud = {
        id: Date.now(),
        laboratorio: document.getElementById("laboratorioSolicitud").value,
        solicitudSoftware: document.getElementById("SolicitudDeSoftware").value,
        detalleSoftware: document.getElementById("DetalleSoftware").value || "Sin detalle",
    }
    const solicitudGuardada = JSON.parse(localStorage.getItem('Solicitudes')) || [];

    solicitudGuardada.push(solicitud);

    localStorage.setItem("Solicitudes", JSON.stringify(solicitudGuardada));
    console.log("Ticket guardado:", solicitud);
    form.reset();
});

solicitudSoftware.addEventListener('change', function() {
    if (this.value === "Si") {
        detalleSoftware.required = true;
        detalleSoftware.disabled = false;
    } else {
        detalleSoftware.required = false;
        detalleSoftware.disabled = true;
        detalleSoftware.value = "";
    }
});

detalleSoftware.disabled = true;
detalleSoftware.required = false;

