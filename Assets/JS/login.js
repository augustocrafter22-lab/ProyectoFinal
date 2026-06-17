//definimos variables y constantes necesarias para la validación del formulario de inicio de sesión
const CI = "11111111"; // Ci predefinida para la validación
const contrasenia = "123456789"; // Contraseña predefinida para la validación
const formulario = document.getElementById("loginForm"); // Obtenemos el formulario por su ID
const inputCI = document.getElementById("username"); // Obtenemos el campo de texto de CI por su ID
const inputContrasenia = document.getElementById("clave"); // Obtenemos el campo de contraseña por su ID
const rememberMe = document.getElementById("remember");
const errorMessageDisplay = document.getElementById("errorMessage");
formulario.addEventListener("submit", function (event) {
  // Agregamos un evento de envío al formulario
  event.preventDefault(); // Evitamos que el formulario se envíe al servidor
  if (inputCI.value === CI && inputContrasenia.value === contrasenia) {
    // Validamos que los valores ingresados coincidan con los predefinidos
    // Aquí iría el código para redirigir al usuario a la página principal
    if (rememberMe.checked) {
      // Si el usuario ha marcado "Recuérdame", guardamos su información en localStorage
      localStorage.setItem("CI", inputCI.value); // Guardamos el CI en localStorage
      localStorage.setItem("contrasenia", inputContrasenia.value); // Guardamos la contraseña en localStorage
    }
    window.location.href = "Principal.html"; // Redirigimos al usuario a la página principal
  } else {
    errorMessageDisplay.textContent = "CI o contraseña incorrectos"; // Mostramos una alerta si los datos son incorrectos
  }
});
