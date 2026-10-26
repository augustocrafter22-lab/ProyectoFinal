/**
 * Traducción de la pantalla (español / inglés).
 *
 * Los textos se piden al back por secciones y solo las que la pantalla usa:
 * la sección "comun" más las que aparezcan en sus atributos data-i18n.
 * En el HTML:
 *   data-i18n="equipos.titulo"                  cambia el texto del elemento
 *   data-i18n-placeholder="equipos.filtroId"    cambia el placeholder
 *   data-i18n-title="seccion.clave"             cambia el atributo title
 *   data-i18n-alt="seccion.clave"               cambia el atributo alt de una imagen
 */

//Textos ya cargados, con la forma { seccion: { clave: texto } }
let traducciones = {};

//Atributo del HTML y propiedad que se traduce con él
const ATRIBUTOS_TRADUCIBLES = [
    { atributo: "data-i18n", propiedad: "textContent" },
    { atributo: "data-i18n-placeholder", propiedad: "placeholder" },
    { atributo: "data-i18n-title", propiedad: "title" },
    { atributo: "data-i18n-alt", propiedad: "alt" }
];

//Averigua qué secciones necesita la pantalla leyendo sus atributos data-i18n
function obtenerSeccionesDePagina() {
    const secciones = new Set(["comun"]);

    for (const { atributo } of ATRIBUTOS_TRADUCIBLES) {
        for (const elemento of document.querySelectorAll(`[${atributo}]`)) {
            secciones.add(elemento.getAttribute(atributo).split(".")[0]);
        }
    }

    //Secciones que solo se usan desde el JS de la pantalla: <body data-secciones="vistaLab,otra">
    for (const seccion of (document.body.dataset.secciones ?? "").split(",").filter(Boolean)) {
        secciones.add(seccion);
    }

    return [...secciones];
}

//Pide las secciones al back, en el idioma guardado en la sesión
async function cargarTraducciones(secciones) {
    const { datos } = await llamarApi("traducciones", { parametros: { secciones: secciones.join(",") } });

    traducciones = datos.secciones;
    document.documentElement.lang = datos.idioma;
}

/**
 * Devuelve un texto a partir de su clave "seccion.clave".
 * Los valores reemplazan a los {marcadores} del texto: traducir("a.b", { ticket: "INC-1" }).
 */
function traducir(clave, valores = {}) {
    const [seccion, nombre] = clave.split(".");
    let texto = traducciones[seccion]?.[nombre] ?? clave;

    for (const marcador in valores) {
        texto = texto.replaceAll(`{${marcador}}`, valores[marcador]);
    }

    return texto;
}

//Reemplaza los textos del HTML por su traducción
function aplicarTraducciones() {
    for (const { atributo, propiedad } of ATRIBUTOS_TRADUCIBLES) {
        for (const elemento of document.querySelectorAll(`[${atributo}]`)) {
            elemento[propiedad] = traducir(elemento.getAttribute(atributo));
        }
    }
}

//Guarda el idioma elegido en la sesión y recarga la pantalla para que todo quede traducido
function cambiarIdioma(idioma) {
    localStorage.setItem("idioma", idioma);
    window.location.reload();
}
