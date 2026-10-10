import { expect, Page, APIRequestContext } from '@playwright/test';

export const BASE_URL = 'http://localhost:3000';
export const URL_PAGINAS = '/PROYECTO/public/paginas';
export const URL_API = '/PROYECTO/public/api';
export const URL_LOGIN = `${URL_PAGINAS}/Login.php`;

export type Usuario = { ci: string; clave: string };

// Clave de los usuarios TEST_ que crea global-setup.ts.
// Ojo: el nombre admite hasta 12 caracteres y el apellido hasta 16.
export const CLAVE_TEST = 'TEST_clave1';

/**
 * Usuarios de prueba.
 * - principal: usuario real con los tres roles, se usa para preparar los datos.
 * - coordinador, tecnico, docente, inactivo: usuarios TEST_ creados por global-setup.ts.
 * - sinRol: la app no permite crear usuarios sin rol, por eso se toma de variables
 *   de entorno (CI_SINROL y CLAVE_SINROL). Si no están, los tests que lo usan se saltean.
 */
export const USUARIOS = {
  principal: { ci: '12345678', clave: '123456' },
  coordinador: { ci: '90000001', clave: CLAVE_TEST },
  tecnico: { ci: '90000002', clave: CLAVE_TEST },
  docente: { ci: '90000004', clave: CLAVE_TEST },
  inactivo: { ci: '90000005', clave: CLAVE_TEST },
  sinRol: process.env.CI_SINROL && process.env.CLAVE_SINROL
    ? { ci: process.env.CI_SINROL, clave: process.env.CLAVE_SINROL }
    : null,
};

/** Datos con los que global-setup.ts crea o resetea los usuarios TEST_. */
export const USUARIOS_TEST = [
  { ...USUARIOS.coordinador, nombre: 'TEST_Coord', roles: ['coordinador'], activo: true },
  { ...USUARIOS.tecnico, nombre: 'TEST_Tecnico', roles: ['tecnico'], activo: true },
  { ...USUARIOS.docente, nombre: 'TEST_Docente', roles: ['docente'], activo: true },
  { ...USUARIOS.inactivo, nombre: 'TEST_Inact', roles: ['docente'], activo: false },
];
export const APELLIDO_TEST = 'TEST_Playwright';

/** Mensaje que muestra el login cuando la cédula o la clave no coinciden. */
export const MENSAJE_CREDENCIALES = 'La cédula o la contraseña son incorrectas.';

/** Completa y envía el formulario de login, sin verificar a dónde redirige. */
export async function enviarLogin(page: Page, ci: string, clave: string) {
  await page.goto(URL_LOGIN);
  await page.locator('#username').fill(ci);
  await page.locator('#clave').fill(clave);
  await page.locator('#loginForm button[type="submit"]').click();
}

/** Inicia sesión por la interfaz y verifica que se haya salido del login. */
export async function login(page: Page, usuario: Usuario) {
  await enviarLogin(page, usuario.ci, usuario.clave);
  await expect(page).not.toHaveURL(/Login\.php/);
}

/** Lee el token CSRF de la página actual (meta csrf-token). */
export async function tokenDePagina(page: Page): Promise<string> {
  const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
  expect(token, 'la página debería tener el meta csrf-token').toBeTruthy();
  return token as string;
}

/** Inicia sesión con un contexto de API (sin navegador). */
export async function loginApi(request: APIRequestContext, usuario: Usuario) {
  const respuesta = await request.post(`${URL_PAGINAS}/procesarLogin.php`, {
    form: { username: usuario.ci, clave: usuario.clave },
  });
  expect(respuesta.url(), `login de ${usuario.ci}`).not.toContain('Login.php');
}

/**
 * Obtiene el token CSRF de la sesión de un contexto de API leyendo una página
 * que tenga el meta csrf-token (IngresoDeTickets para docente, VistaDeTickets para técnico).
 */
export async function tokenApi(request: APIRequestContext, pagina = 'IngresoDeTickets.php'): Promise<string> {
  const html = await (await request.get(`${URL_PAGINAS}/${pagina}`)).text();
  const coincidencia = html.match(/name="csrf-token" content="([^"]+)"/);
  expect(coincidencia, `${pagina} debería tener el meta csrf-token`).not.toBeNull();
  return (coincidencia as RegExpMatchArray)[1];
}

/** Genera una cédula de 8 dígitos que empieza con 9 para datos de prueba descartables. */
export function cedulaAleatoria(): string {
  return '9' + String(Math.floor(Math.random() * 10_000_000)).padStart(7, '0');
}
