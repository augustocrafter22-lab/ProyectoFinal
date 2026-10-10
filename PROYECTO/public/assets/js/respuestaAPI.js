// Lee la respuesta de la API y devuelve lo que viene bajo la clave "data"
async function leerRespuestaAPI(respuesta) {
  const texto = await respuesta.text();

  if (!texto.trim()) {
    throw new Error(`La API respondió sin cuerpo (HTTP ${respuesta.status}).`);
  }

  let json;
  try {
    json = JSON.parse(texto);
  } catch {
    throw new Error(`HTTP ${respuesta.status}: La API no devolvió JSON.`);
  }

  if (!respuesta.ok) {
    throw new Error(`HTTP ${respuesta.status}: ${json.message ?? "La solicitud no se pudo completar."}`);
  }

  return json.data;
}
