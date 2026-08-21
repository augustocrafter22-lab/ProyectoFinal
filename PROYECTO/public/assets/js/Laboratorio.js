const form = document.getElementById("LabForm");
const solicitudSoftware = document.getElementById("SolicitudDeSoftware");
const detalleSoftware = document.getElementById("DetalleSoftware");
const ahora = new Date();
const hoy = ahora.toISOString().split("T")[0];
const horaActual = ahora.toTimeString().slice(0, 5);
const fecha = document.getElementById("FechaEstimada");
const hora = document.getElementById("HoraEstimada");
const restricciones = document.getElementById("Restricciones");

fecha.min = hoy;
hora.min = horaActual;

fecha.addEventListener("change", function () {
    if (this.value === hoy) {
        hora.min = horaActual;
    } else {
        hora.min = "00:00";
    }
});

solicitudSoftware.addEventListener('change', function () {
    if (this.value === "Si") {
        detalleSoftware.required = true;
        detalleSoftware.disabled = false;
        restricciones.required = true;
        restricciones.disabled = false;
    } else {
        detalleSoftware.required = false;
        detalleSoftware.disabled = true;
        detalleSoftware.value = "";
        restricciones.required = false;
        restricciones.disabled = true;
        restricciones.value = "";
    }
});

detalleSoftware.disabled = true;
detalleSoftware.required = false;
restricciones.disabled = true;
restricciones.required = false;