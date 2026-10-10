import { test, expect, Page } from '@playwright/test';
import { USUARIOS, URL_LOGIN, URL_PAGINAS, MENSAJE_CREDENCIALES, login, enviarLogin } from './helpers';

const MENSAJE_INACTIVO = 'El usuario se encuentra inactivo.';
const MENSAJE_SIN_ROLES = 'Usuario sin roles habilitados';
const MENSAJE_FORMATO_CI = 'La cédula debe tener 8 dígitos numéricos.';

async function verificarQueNoSeEnvio(page: Page) {
  await expect(page).toHaveURL(new RegExp(`${URL_LOGIN}$`));
  await expect(page.locator('#errorMessage')).toHaveCount(0);
}

test.describe('Login SGRSI', () => {
  test('LOG-01 coordinador entra a Administrador.php', async ({ page }) => {
    await login(page, USUARIOS.coordinador);

    await expect(page).toHaveURL(/\/Administrador\.php$/);
    await expect(page.locator('section.encabezado h1')).toHaveText('Bienvenido, coordinador');
  });

  test('LOG-02 técnico entra a Tecnico.php', async ({ page }) => {
    await login(page, USUARIOS.tecnico);

    await expect(page).toHaveURL(/\/Tecnico\.php$/);
    await expect(page.locator('section.encabezado h1')).toContainText('(Tecnico)');
  });

  test('LOG-03 docente entra a Docente.php', async ({ page }) => {
    await login(page, USUARIOS.docente);

    await expect(page).toHaveURL(/\/Docente\.php$/);
    await expect(page.locator('section.encabezado h1')).toContainText('TEST_Docente');
    await expect(page.locator('section.encabezado h1')).toContainText('(Docente)');
  });

  test('LOG-04 usuario con varios roles entra a PanelRoles.php', async ({ page }) => {
    await login(page, USUARIOS.principal);

    await expect(page).toHaveURL(/\/PanelRoles\.php$/);
    await expect(page.getByText('Seleccione el rol con el que desea ingresar')).toBeVisible();
    const botonera = page.locator('section.botonera');
    await expect(botonera.getByRole('button', { name: 'Coordinador' })).toBeVisible();
    await expect(botonera.getByRole('button', { name: 'Técnico' })).toBeVisible();
    await expect(botonera.getByRole('button', { name: 'Docente' })).toBeVisible();
  });

  test('LOG-05 desde PanelRoles se puede elegir cada rol', async ({ page }) => {
    await login(page, USUARIOS.principal);
    const botonera = page.locator('section.botonera');

    await botonera.getByRole('button', { name: 'Coordinador' }).click();
    await expect(page).toHaveURL(/\/Administrador\.php$/);

    await page.goto(`${URL_PAGINAS}/PanelRoles.php`);
    await botonera.getByRole('button', { name: 'Técnico' }).click();
    await expect(page).toHaveURL(/\/Tecnico\.php$/);

    await page.goto(`${URL_PAGINAS}/PanelRoles.php`);
    await botonera.getByRole('button', { name: 'Docente' }).click();
    await expect(page).toHaveURL(/\/Docente\.php$/);
  });

  test('LOG-06 usuario con un solo rol que entra a PanelRoles va a su panel', async ({ page }) => {
    await login(page, USUARIOS.docente);

    await page.goto(`${URL_PAGINAS}/PanelRoles.php`);

    await expect(page).toHaveURL(/\/Docente\.php$/);
  });

  test('LOG-07 usuario sin rol muestra error', async ({ page }) => {
    test.skip(USUARIOS.sinRol === null, 'la app no permite crear usuarios sin rol; definí CI_SINROL y CLAVE_SINROL');
    const sinRol = USUARIOS.sinRol!;

    await enviarLogin(page, sinRol.ci, sinRol.clave);

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_SIN_ROLES);
  });

  test('LOG-08 usuario inactivo muestra error', async ({ page }) => {
    await enviarLogin(page, USUARIOS.inactivo.ci, USUARIOS.inactivo.clave);

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_INACTIVO);
  });

  test('LOG-09 campos vacíos no envían el formulario', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await page.getByRole('button', { name: 'Ingresar' }).click();

    await verificarQueNoSeEnvio(page);
    expect(await page.locator('#username').evaluate((input: HTMLInputElement) => input.validity.valueMissing)).toBe(true);
    expect(await page.locator('#clave').evaluate((input: HTMLInputElement) => input.validity.valueMissing)).toBe(true);
  });

  test('LOG-10 CI completa y clave vacía no envía el formulario', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await page.locator('#username').fill(USUARIOS.docente.ci);
    await page.getByRole('button', { name: 'Ingresar' }).click();

    await verificarQueNoSeEnvio(page);
    expect(await page.locator('#clave').evaluate((input: HTMLInputElement) => input.validity.valueMissing)).toBe(true);
  });

  test('LOG-11 CI inexistente muestra error de credenciales', async ({ page }) => {
    // Cédula de 8 dígitos que empieza con 0, el rango 9xxxxxxx lo usan los datos de prueba.
    await enviarLogin(page, '00000001', 'TEST_clave1');

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_CREDENCIALES);
  });

  test('LOG-12 CI de 6 dígitos no envía el formulario', async ({ page }) => {
    await page.goto(URL_LOGIN);
    await page.locator('#username').fill('123456');
    await page.locator('#clave').fill('TEST_clave1');
    await page.getByRole('button', { name: 'Ingresar' }).click();

    await verificarQueNoSeEnvio(page);
    expect(await page.locator('#username').evaluate((input: HTMLInputElement) => input.validity.tooShort)).toBe(true);
  });

  // DEFECTO: procesarLogin.php solo valida la cédula con Validador::requerido (app/controlador/procesarLogin.php:29)
  // y no con Validador::cedula (app/modelo/Validador.php:56); además el input acepta 7 dígitos (app/vista/login.php:31, minlength="7").
  // Una CI de 7 dígitos se consulta en la base y devuelve "credenciales incorrectas" en vez del error de formato.
  test('LOG-13 CI de 7 dígitos se rechaza por formato', async ({ page }) => {
    await enviarLogin(page, '1234567', 'TEST_clave1');

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_FORMATO_CI);
  });
});
