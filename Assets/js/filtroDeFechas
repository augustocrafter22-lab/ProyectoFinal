const fechaDesde = document.getElementById("fechaDesde");
const fechaHasta = document.getElementById("fechaHasta");
const filtrarFechas = document.getElementById("filtrarFechas");

filtrarFechas.addEventListener("click", function() {

    const desde = new Date(fechaDesde.value);
    const hasta = new Date(fechaHasta.value);

    const ticketsVista = document.querySelectorAll(".ticket");

    ticketsVista.forEach(function(article) {

        const fechaTicketTexto = article.dataset.fecha;
        const fechaTicket = new Date(fechaTicketTexto);

        if (fechaTicket >= desde && fechaTicket <= hasta) {
            article.style.display = "flex";
        } else {
            article.style.display = "none";
        }

    });

});