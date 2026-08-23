function actualizarTicket(articulo) {

    const idTicket = articulo.dataset.id;
    const estado = articulo.querySelector(".select-estado").value;
    const prioridad = articulo.querySelector(".select-prioridad").value;

    fetch("procesarActualizarTicket.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ idTicket, estado, prioridad })
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

document.querySelectorAll(".ticket").forEach(articulo => {

    articulo.querySelector(".select-estado").addEventListener("change", () => actualizarTicket(articulo));
    articulo.querySelector(".select-prioridad").addEventListener("change", () => actualizarTicket(articulo));

});
