/* Credenciales fijas para la validación del login.
   Se mantienen en memoria local, no viajan al servidor. */
const CI = "11111111";
const contrasenia = "123456789";
const formulario = document.getElementById("loginForm");
const inputCI = document.getElementById("username");
const inputContrasenia = document.getElementById("clave");
const rememberMe = document.getElementById("remember");
const errorMessageDisplay = document.getElementById("errorMessage");

formulario.addEventListener("submit", function (event) {
  event.preventDefault();
  /* Limpia mensajes previos para evitar acumulación visual. */
  errorMessageDisplay.textContent = "";

  if (inputCI.value === CI && inputContrasenia.value === contrasenia) {
    /* "Recuérdame" persiste credenciales en localStorage para evitar
       escritura repetitiva en futuras sesiones del mismo navegador. */
    if (rememberMe.checked) {
      localStorage.setItem("CI", inputCI.value);
      localStorage.setItem("contrasenia", inputContrasenia.value);
    }
    window.location.href = "Principal.html";
  } else {
    errorMessageDisplay.textContent = "CI o contraseña incorrectos";
  }
});
