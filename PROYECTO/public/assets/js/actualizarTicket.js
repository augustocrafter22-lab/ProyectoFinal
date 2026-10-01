const listaTicketsParaActualizar = document.getElementById("listaTickets");

// Deja los selects del ticket en el último valor guardado en la base.
function restaurarValoresGuardados(selects) {
    for (const select of selects) {
        select.value = select.dataset.valorGuardado;
    }
}

function actualizarTicket(articulo) {

    const idTicket = articulo.dataset.id;
    const selectEstado = articulo.querySelector(".select-estado");
    const selectPrioridad = articulo.querySelector(".select-prioridad");
    const estado = selectEstado.value;
    const prioridad = selectPrioridad.value;

    fetch(`api/tickets.php?id=${encodeURIComponent(idTicket)}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ estado, prioridad })
    })
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (!datos.exito) {
                restaurarValoresGuardados([selectEstado, selectPrioridad]);
                alert(datos.mensaje || "No se pudo actualizar el ticket.");
                return;
            }

            selectEstado.dataset.valorGuardado = estado;
            selectPrioridad.dataset.valorGuardado = prioridad;
        })
        .catch(() => {
            restaurarValoresGuardados([selectEstado, selectPrioridad]);
            alert("Error de conexión al actualizar el ticket.");
        });
}

listaTicketsParaActualizar.addEventListener("change", evento => {
    if (evento.target.matches(".select-estado, .select-prioridad")) {
        actualizarTicket(evento.target.closest(".ticket"));
    }
});
