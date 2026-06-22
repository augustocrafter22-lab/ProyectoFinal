const usuarioActivo = JSON.parse(localStorage.getItem("usuarioActivo"));

if (!usuarioActivo) {
    window.location.href = "../../HTML/Login.html";
}

console.log(usuarioActivo.rol);