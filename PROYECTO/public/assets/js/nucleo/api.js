/**
 * Comunicación con el back: todas las pantallas hablan con la API por acá.
 */

//CUIDADO = La ruta es relativa al lugar donde se cargó el HTML (public/paginas)
const URL_API = "../index.php";

//Arma la URL de un endpoint. Los parámetros vacíos no se envían.
function construirUrlApi(ruta, parametros) {
    const url = new URL(URL_API, window.location.href);
    url.searchParams.set("ruta", ruta);

    for (const nombre in parametros) {
        const valor = parametros[nombre];

        if (valor !== "" && valor !== null && valor !== undefined) {
            url.searchParams.set(nombre, valor);
        }
    }

    return url;
}

//Crea un error que además guarda el código HTTP de la respuesta
function crearErrorApi(mensaje, estado) {
    const error = new Error(mensaje);
    error.estado = estado;

    return error;
}

//Lee la respuesta de la API, que siempre es { status, message, data }
async function leerRespuestaAPI(respuesta) {
    const texto = await respuesta.text();

    let json;
    try {
        json = JSON.parse(texto);
    } catch {
        throw crearErrorApi(`${traducir("comun.errorRespuestaApi")} (HTTP ${respuesta.status})`, respuesta.status);
    }

    if (!respuesta.ok) {
        throw crearErrorApi(json.message ?? traducir("comun.errorGenerico"), respuesta.status);
    }

    return { mensaje: json.message, datos: json.data };
}

/**
 * Hace una solicitud a un endpoint de la API.
 *
 * @param {string} ruta Nombre del endpoint (equipos, tickets, usuarios...).
 * @param {object} opciones metodo (GET por defecto), parametros de la URL y cuerpo (se envía como JSON).
 * @returns {Promise<{mensaje: string, datos: any}>} Lo que respondió la API.
 */
async function llamarApi(ruta, { metodo = "GET", parametros = {}, cuerpo = null } = {}) {
    const opciones = { method: metodo, headers: {} };

    //Solo las operaciones que modifican datos llevan el token CSRF
    if (metodo !== "GET") {
        opciones.headers["X-CSRF-Token"] = sessionStorage.getItem("csrfToken") ?? "";
    }

    if (cuerpo !== null) {
        opciones.headers["Content-Type"] = "application/json";
        opciones.body = JSON.stringify(cuerpo);
    }

    let respuesta;
    try {
        respuesta = await fetch(construirUrlApi(ruta, parametros), opciones);
    } catch {
        throw crearErrorApi(traducir("comun.errorConexion"), 0);
    }

    try {
        return await leerRespuestaAPI(respuesta);
    } catch (error) {
        //Una sesión vencida lleva siempre al login, salvo en el propio login
        if (error.estado === 401 && ruta !== "login") {
            await irALogin("sesionRequerida");
        }

        throw error;
    }
}
