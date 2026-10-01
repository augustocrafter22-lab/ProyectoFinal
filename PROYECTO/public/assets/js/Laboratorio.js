const form = document.getElementById("LabForm");
const solicitudSoftware = document.getElementById("SolicitudDeSoftware");
const detalleSoftware = document.getElementById("DetalleSoftware");
const ahora = new Date();
// Fecha de hoy en hora local (toISOString usa UTC y después de las 21:00 en Uruguay da el día siguiente).
let mesActual = ahora.getMonth() + 1;
let diaActual = ahora.getDate();
if (mesActual < 10) {
    mesActual = "0" + mesActual;
}
if (diaActual < 10) {
    diaActual = "0" + diaActual;
}
const hoy = `${ahora.getFullYear()}-${mesActual}-${diaActual}`;
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