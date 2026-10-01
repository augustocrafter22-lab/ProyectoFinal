const listaTicketsParaActualizar = document.getElementById("listaTickets");

function actualizarTicket(articulo) {

    const idTicket = articulo.dataset.id;
    const estado = articulo.querySelector(".select-estado").value;
    const prioridad = articulo.querySelector(".select-prioridad").value;

    fetch(`api/tickets.php?id=${encodeURIComponent(idTicket)}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ estado, prioridad })
    })
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (!datos.exito) {
                alert(datos.mensaje || "No se pudo actualizar el ticket.");
            }
        })
        .catch(() => {
            alert("Error de conexión al actualizar el ticket.");
        });
}

listaTicketsParaActualizar.addEventListener("change", evento => {
    if (evento.target.matches(".select-estado, .select-prioridad")) {
        actualizarTicket(evento.target.closest(".ticket"));
    }
});
