/**
 * Sesión del usuario en el navegador: quién es, qué roles tiene y cómo cierra la sesión.
 */

//Datos de la sesión que devolvió la API (cedula, nombre, apellido, roles, idioma)
let datosSesion = null;

//Consulta la sesión al servidor y guarda el token CSRF que usarán las operaciones de la pantalla
async function cargarSesion() {
    const { datos } = await llamarApi("sesion");

    datosSesion = datos;

    return datos;
}

//Indica si el usuario tiene alguno de los roles recibidos
function tieneAlgunRol(roles) {
    return roles.some((rol) => datosSesion.roles.includes(rol));
}

/**
 * Lleva al login indicando el motivo, que el login muestra traducido.
 * Devuelve una promesa que nunca termina, para que la pantalla no siga cargando.
 */
function irALogin(motivo) {
    const url = new URL("login.html", window.location.href);

    if (motivo) {
        url.searchParams.set("motivo", motivo);
    }

    //replace() abre la siguiente página borrando el historial de la previa
    window.location.replace(url);

    return new Promise(() => {});
}

async function cerrarSesion() {
    try {
        await llamarApi("logout", { metodo: "POST" });

        window.location.replace("login.html");
    } catch {
        //Si no se pudo cerrar la sesión en el servidor, igual se borra la sesión local
    }

    sessionStorage.removeItem("token");
    window.location.replace("login.html");
}
