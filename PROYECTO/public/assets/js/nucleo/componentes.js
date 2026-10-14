/**
 * Piezas de pantalla que se repiten en varias páginas: mensajes, listas desplegables y botones.
 */

//Muestra un aviso al usuario; el color depende de si es un error o un éxito
function mostrarMensaje(elementoMensaje, texto, esError) {
    elementoMensaje.textContent = texto;
    elementoMensaje.className = esError ? "mensaje mensajeError" : "mensaje mensajeExito";
}

//Borra el aviso
function ocultarMensaje(elementoMensaje) {
    elementoMensaje.textContent = "";
    elementoMensaje.className = "mensaje";
}

/**
 * Agrega una opción por cada elemento de la lista.
 *
 * @param {HTMLSelectElement} select Lista desplegable a completar.
 * @param {Array} lista Elementos recibidos de la API.
 * @param {function} obtenerValor Recibe un elemento y devuelve el value de la opción.
 * @param {function} obtenerTexto Recibe un elemento y devuelve el texto de la opción.
 */
function agregarOpciones(select, lista, obtenerValor, obtenerTexto) {
    for (const elemento of lista) {
        const opcion = document.createElement("option");
        opcion.value = obtenerValor(elemento);
        opcion.textContent = obtenerTexto(elemento);
        select.appendChild(opcion);
    }
}

//Deja solo la primera opción ("Seleccione...") de una lista desplegable
function vaciarOpciones(select) {
    select.options.length = 1;
}

function crearBoton(texto, claseCss, accion) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.className = claseCss;
    boton.textContent = texto;
    boton.addEventListener("click", accion);

    return boton;
}

//Crea una celda de tabla con un texto
function crearCelda(texto) {
    const celda = document.createElement("td");
    celda.textContent = texto;

    return celda;
}

//Fecha de hoy con formato AAAA-MM-DD, según la hora de la computadora
function obtenerFechaHoy() {
    const hoy = new Date();
    const mes = String(hoy.getMonth() + 1).padStart(2, "0");
    const dia = String(hoy.getDate()).padStart(2, "0");

    return `${hoy.getFullYear()}-${mes}-${dia}`;
}
