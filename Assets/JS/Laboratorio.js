const form = document.getElementById("LabForm");
const solicitudSoftware = document.getElementById("SolicitudDeSoftware");
const detalleSoftware = document.getElementById("DetalleSoftware");
const ahora = new Date();
const hoy = ahora.toISOString().split("T")[0];
const horaActual = ahora.toTimeString().slice(0, 5);
const fecha = document.getElementById("FechaEstimada");
const hora = document.getElementById("HoraEstimada");

fecha.min = hoy;
hora.min = horaActual;

fecha.addEventListener("change", function () {
    if (this.value === hoy) {
        hora.min = horaActual;
    } else {
        hora.min = "00:00";
    }
});

form.addEventListener('submit', function (e) {
    e.preventDefault();

    const solicitud = {
        id: Date.now(),
        laboratorio: document.getElementById("laboratorioSolicitud").value,
        solicitudSoftware: document.getElementById("SolicitudDeSoftware").value,
        detalleSoftware: document.getElementById("DetalleSoftware").value || "Sin detalle",
        fechaEstimada: fecha.value,
        horaEstimada: hora.value,
    }

    const solicitudGuardada = JSON.parse(localStorage.getItem('Solicitudes')) || [];
    solicitudGuardada.push(solicitud);
    localStorage.setItem("Solicitudes", JSON.stringify(solicitudGuardada));
    console.log("Ticket guardado:", solicitud);
    form.reset();
    document.getElementById("dialogConfirmacion").showModal();
});

solicitudSoftware.addEventListener('change', function () {
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

