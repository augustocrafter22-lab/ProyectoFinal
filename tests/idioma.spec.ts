import { test, expect, Page } from '@playwright/test';
import { USUARIOS, URL_LOGIN, URL_PAGINAS, MENSAJE_CREDENCIALES, login, enviarLogin } from './helpers';

const MENSAJE_SIN_SESION = 'Debe iniciar sesión para acceder a esa página.';

async function verificarLoginEnEspanol(page: Page) {
  await expect(page.locator('section.SGRSI p')).toHaveText('Sistema de Gestión de Recursos y Soporte de Informática');
  await expect(page.locator('#loginForm h2')).toHaveText('Iniciar Sesión');
  await expect(page.locator('label[for="username"]')).toHaveText('CI:');
  await expect(page.locator('label[for="clave"]')).toHaveText('Contraseña:');
  await expect(page.locator('#loginForm button[type="submit"]')).toHaveText('Ingresar');
}

async function verificarLoginEnIngles(page: Page) {
  await expect(page.locator('section.SGRSI p')).toHaveText('Information Resources and Support Management System');
  await expect(page.locator('#loginForm h2')).toHaveText('Log In');
  await expect(page.locator('label[for="username"]')).toHaveText('ID:');
  await expect(page.locator('label[for="clave"]')).toHaveText('Password:');
  await expect(page.locator('#loginForm button[type="submit"]')).toHaveText('Log in');
}

/** Cambia el idioma con el enlace ES/EN de la página actual (el selector del login o la barra de navegación). */
async function cambiarIdioma(page: Page, idioma: 'ES' | 'EN') {
  const urlAnterior = page.url();
  await page.getByRole('link', { name: idioma, exact: true }).click();
  await expect(page).toHaveURL(urlAnterior);
}

async function loginDocenteEnIngles(page: Page) {
  await login(page, USUARIOS.docente);
  await expect(page).toHaveURL(/\/Docente\.php$/);
  await cambiarIdioma(page, 'EN');
}

test.describe('Idioma SGRSI', () => {
  test('IDI-01 en el login cambiar a EN traduce los textos y volver a ES los restaura', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await verificarLoginEnEspanol(page);

    await cambiarIdioma(page, 'EN');
    await verificarLoginEnIngles(page);

    await cambiarIdioma(page, 'ES');
    await verificarLoginEnEspanol(page);
  });

  test('IDI-02 en el panel docente cambiar el idioma desde la barra de navegación', async ({ page }) => {
    await loginDocenteEnIngles(page);

    const encabezado = page.locator('section.encabezado h1');
    const navegacion = page.locator('.listaNavegacion');
    await expect(encabezado).toContainText('Welcome,');
    await expect(encabezado).toContainText('(Teacher)');
    await expect(page.locator('section.modulo-imagen p')).toHaveText('Use the menu to get started.');
    await expect(navegacion.getByRole('link', { name: 'Request lab' })).toBeVisible();
    await expect(navegacion.getByRole('link', { name: 'Submit Ticket' })).toBeVisible();
    await expect(navegacion.getByRole('link', { name: 'Log out' })).toBeVisible();

    await cambiarIdioma(page, 'ES');
    await expect(encabezado).toContainText('Bienvenido,');
    await expect(encabezado).toContainText('(Docente)');
    await expect(navegacion.getByRole('link', { name: 'Solicitar laboratorio' })).toBeVisible();
    await expect(navegacion.getByRole('link', { name: 'Cerrar sesion' })).toBeVisible();
  });

  test('IDI-03 el idioma se mantiene al navegar a otras páginas', async ({ page }) => {
    await loginDocenteEnIngles(page);

    await page.locator('.listaNavegacion').getByRole('link', { name: 'Submit Ticket' }).click();
    await expect(page).toHaveURL(/\/IngresoDeTickets\.php$/);
    await expect(page.locator('section.encabezado h1')).toHaveText('New Ticket');
    await expect(page.locator('label[for="asunto"]')).toHaveText('Subject:');
    await expect(page.locator('#ticketForm button[type="submit"]')).toHaveText('Submit Ticket');

    await page.locator('.listaNavegacion').getByRole('link', { name: 'Back' }).click();
    await expect(page).toHaveURL(/\/Docente\.php$/);
    await expect(page.locator('section.encabezado h1')).toContainText('(Teacher)');

    await page.goto(`${URL_PAGINAS}/SolicitarLab.php`);
    await expect(page.locator('section.encabezado h1')).toHaveText('Lab Request');
  });

  test('IDI-04 el idioma elegido en el login se mantiene después de iniciar sesión', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await cambiarIdioma(page, 'EN');

    await page.locator('#username').fill(USUARIOS.docente.ci);
    await page.locator('#clave').fill(USUARIOS.docente.clave);
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page).toHaveURL(/\/Docente\.php$/);
    await expect(page.locator('section.encabezado h1')).toContainText('(Teacher)');
  });

  test('IDI-05 un idioma inválido cae en español', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await cambiarIdioma(page, 'EN');
    await verificarLoginEnIngles(page);

    await page.goto(`${URL_PAGINAS}/cambiarIdioma.php?idioma=xx`);

    await expect(page).toHaveURL(/\/Login\.php$/);
    await verificarLoginEnEspanol(page);
  });

  test('IDI-06 un idioma inválido dentro de un panel también cae en español', async ({ page }) => {
    await loginDocenteEnIngles(page);

    await page.goto(`${URL_PAGINAS}/cambiarIdioma.php?idioma=xx`);
    await page.goto(`${URL_PAGINAS}/Docente.php`);

    await expect(page.locator('section.encabezado h1')).toContainText('(Docente)');
  });

  // DEFECTO: los mensajes de error del login están fijos en español en el modelo
  // (app/modelo/Login.php:25, 30 y 35) y no pasan por Traductor::t, en en.php no hay clave para ellos.
  test('IDI-07 en inglés el error de credenciales del login se muestra en inglés', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await cambiarIdioma(page, 'EN');

    await page.locator('#username').fill('00000001');
    await page.locator('#clave').fill('TEST_clave1');
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await verificarLoginEnIngles(page);
    await expect(page.locator('#errorMessage')).toBeVisible();
    await expect(page.locator('#errorMessage')).not.toHaveText(MENSAJE_CREDENCIALES);
  });

  // DEFECTO: los mensajes de Sesion::verificarSesion y verificarRol están fijos en español
  // (app/nucleo/Sesion.php:20 y 38) y no usan Traductor::t.
  test('IDI-08 en inglés el aviso de sesión requerida se muestra en inglés', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await cambiarIdioma(page, 'EN');

    await page.goto(`${URL_PAGINAS}/Docente.php`);

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await verificarLoginEnIngles(page);
    await expect(page.locator('#errorMessage')).toBeVisible();
    await expect(page.locator('#errorMessage')).not.toHaveText(MENSAJE_SIN_SESION);
  });

  // DEFECTO: las opciones de turno están escritas en español en la vista (app/vista/ingresoTickets.php:68-70)
  // y no usan Traductor::t, quedan "Matutino/Vespertino/Nocturno" con la interfaz en inglés.
  test('IDI-09 en inglés las opciones de turno de IngresoDeTickets se traducen', async ({ page }) => {
    await loginDocenteEnIngles(page);
    await page.goto(`${URL_PAGINAS}/IngresoDeTickets.php`);
    await expect(page.locator('label[for="turno"]')).toHaveText('Shift when the incident was reported:');

    const opciones = page.locator('#turno option');
    await expect(opciones.first()).toHaveText('Select a shift');
    await expect(opciones.nth(1)).not.toHaveText('Matutino');
    await expect(opciones.nth(2)).not.toHaveText('Vespertino');
    await expect(opciones.nth(3)).not.toHaveText('Nocturno');
  });

  // DEFECTO: el <title> de las vistas está fijo en español (app/vista/docente.php:7 "Docente",
  // app/vista/ingresoTickets.php:7 "Ingreso de Tickets"), la pestaña no cambia con el idioma.
  test('IDI-10 en inglés el título de la pestaña del panel docente se traduce', async ({ page }) => {
    await loginDocenteEnIngles(page);

    await expect(page).toHaveTitle('Teacher');
  });
});
