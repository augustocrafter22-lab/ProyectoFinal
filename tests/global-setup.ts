import { request } from '@playwright/test';
import { BASE_URL, URL_API, USUARIOS, USUARIOS_TEST, APELLIDO_TEST, loginApi, tokenApi } from './helpers';

/**
 * Crea (o deja como nuevos) los usuarios TEST_ que usan los tests,
 * usando la API de usuarios con el usuario principal (coordinador).
 */
export default async function globalSetup() {
  const contexto = await request.newContext({ baseURL: BASE_URL });
  await loginApi(contexto, USUARIOS.principal);
  const token = await tokenApi(contexto);

  for (const usuario of USUARIOS_TEST) {
    const existente = await contexto.get(`${URL_API}/usuarios.php?id=${usuario.ci}`);

    if (existente.status() === 404) {
      const alta = await contexto.post(`${URL_API}/usuarios.php`, {
        headers: { 'X-CSRF-Token': token },
        data: { cedula: usuario.ci, nombre: usuario.nombre, apellido: APELLIDO_TEST, clave: usuario.clave, roles: usuario.roles },
      });
      if (alta.status() !== 201) {
        throw new Error(`No se pudo crear ${usuario.ci}: ${alta.status()} ${await alta.text()}`);
      }
    }

    const cambios = await contexto.put(`${URL_API}/usuarios.php?id=${usuario.ci}`, {
      headers: { 'X-CSRF-Token': token },
      data: { nombre: usuario.nombre, apellido: APELLIDO_TEST, clave: usuario.clave, roles: usuario.roles, activo: usuario.activo },
    });
    if (cambios.status() !== 200) {
      throw new Error(`No se pudo resetear ${usuario.ci}: ${cambios.status()} ${await cambios.text()}`);
    }
  }

  await contexto.dispose();
}
