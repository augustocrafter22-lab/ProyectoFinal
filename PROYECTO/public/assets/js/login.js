const formularioLogin = document.getElementById("formularioLogin");

const campoCedula = document.getElementById("cedula");
const campoClave = document.getElementById("clave");
const mensajeLogin = document.getElementById("mensajeLogin");

//POST - Envía las credenciales y devuelve los datos de la sesión iniciada
async function loguear(cedula, clave) {
    const { datos } = await llamarApi("login", {
        metodo: "POST",
        cuerpo: { cedula: cedula, clave: clave }
    });

    return datos;
}

//Decide a qué pantalla va el usuario según los roles que tiene habilitados
function obtenerPaginaInicio(roles) {
    if (roles.length > 1) {
        return "panelRoles.html";
    }

    if (roles.includes("coordinador")) {
        return "administrador.html";
    }

    if (roles.includes("tecnico")) {
        return "tecnico.html";
    }

    return "docente.html";
}

//Si el sistema mandó al login por una sesión vencida o un rol incorrecto, lo explica
function mostrarMotivoDeRedireccion() {
    const motivo = new URLSearchParams(window.location.search).get("motivo");

    if (motivo === "sesionRequerida" || motivo === "sinAutorizacion") {
        mensajeLogin.textContent = traducir(`login.${motivo}`);
    }
}

async function gestionarLogin(eventoFormulario) {
    eventoFormulario.preventDefault();
    mensajeLogin.textContent = "";

    try {
        const sesion = await loguear(campoCedula.value.trim(), campoClave.value);

        sessionStorage.setItem("csrfToken", sesion.csrfToken);
        //replace() abre la siguiente página borrando el historial de la previa
        window.location.replace(obtenerPaginaInicio(sesion.roles));
    } catch (error) {
        mensajeLogin.textContent = error.message;
    }
}

formularioLogin.addEventListener("submit", gestionarLogin);

async function iniciar() {
    await iniciarPagina();
    mostrarMotivoDeRedireccion();
}

iniciar();
