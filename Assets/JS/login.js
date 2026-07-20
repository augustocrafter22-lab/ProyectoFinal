const formulario = document.getElementById("loginForm");
const inputCI = document.getElementById("username");
const inputContrasenia = document.getElementById("clave");
const errorMessageDisplay = document.getElementById("errorMessage");

function inicializarUsuarioRaiz() {
  const usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];
  const raizExiste = usuarios.some(u => u.ci === "11111111");

  if (!raizExiste) {
    usuarios.push({
      ci: "11111111",
      contrasenia: "123456789",
      rol: "coordinador"
    });
    localStorage.setItem("usuarios", JSON.stringify(usuarios));
  }
}

inicializarUsuarioRaiz();

formulario.addEventListener("submit", function (event) {
  event.preventDefault();
  errorMessageDisplay.textContent = "";

  const usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];
  const usuarioEncontrado = usuarios.find(
    u => u.ci === inputCI.value && u.contrasenia === inputContrasenia.value
  );

  if (usuarioEncontrado) {
    localStorage.setItem("usuarioActivo", JSON.stringify(usuarioEncontrado));

    if (usuarioEncontrado.rol === "coordinador") {
      window.location.href = "../../HTML/Coordinador.html";
    } else if (usuarioEncontrado.rol === "tecnico") {
      window.location.href = "Tecnico.html";
    } else if (usuarioEncontrado.rol === "solicitante") {
      window.location.href = "Solicitante.html";
    }
  } else {
    errorMessageDisplay.textContent = "CI o contraseña incorrectos";
  }
});