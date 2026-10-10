import { test, expect, Page, APIRequestContext, Browser } from '@playwright/test';
import {
  BASE_URL, URL_API, URL_PAGINAS, USUARIOS, CLAVE_TEST, APELLIDO_TEST, MENSAJE_CREDENCIALES,
  Usuario, login, enviarLogin, loginApi, tokenApi, cedulaAleatoria,
} from './helpers';

const URL_ADMINISTRADOR = `${URL_PAGINAS}/Administrador.php`;
const URL_API_USUARIOS = `${URL_API}/usuarios.php`;

const MENSAJE_CREADO = 'Usuario creado exitosamente';
const MENSAJE_ACTUALIZADO = 'Usuario actualizado exitosamente';
const MENSAJE_DESACTIVADO = 'Usuario desactivado exitosamente';
const MENSAJE_ACTIVADO = 'Usuario Activado exitosamente';
const MENSAJE_INACTIVO = 'El usuario se encuentra inactivo.';
const MENSAJE_FORMATO_CI = 'La cédula debe tener 8 dígitos numéricos.';
const MENSAJE_LARGO_NOMBRE = 'El campo nombre debe tener entre 1 y 12 caracteres.';
const MENSAJE_LARGO_APELLIDO = 'El campo apellido debe tener entre 1 y 16 caracteres.';
const MENSAJE_SIN_ROL = 'Debe seleccionar al menos un rol.';

type DatosUsuario = { ci: string; nombre: string; apellido: string; clave: string; roles: string[] };

// Cédulas creadas por el test en curso, se borran en afterEach.
let creados: string[] = [];
let token = '';

function nuevoUsuario(roles: string[] = ['docente']): DatosUsuario {
  const ci = cedulaAleatoria();
  return { ci, nombre: `TEST_${ci.slice(-6)}`, apellido: APELLIDO_TEST, clave: CLAVE_TEST, roles };
}

async function crearUsuarioApi(request: APIRequestContext, datos: DatosUsuario) {
  creados.push(datos.ci);
  const respuesta = await request.post(URL_API_USUARIOS, {
    headers: { 'X-CSRF-Token': token },
    data: { cedula: datos.ci, nombre: datos.nombre, apellido: datos.apellido, clave: datos.clave, roles: datos.roles },
  });
  expect(respuesta.status(), await respuesta.text()).toBe(201);
}

async function obtenerUsuarioApi(request: APIRequestContext, ci: string) {
  const respuesta = await request.get(`${URL_API_USUARIOS}?id=${ci}`);
  if (respuesta.status() === 404) {
    return null;
  }
  expect(respuesta.status()).toBe(200);
  return (await respuesta.json()).datos;
}

function filaDe(page: Page, ci: string) {
  return page.locator('#cuerpoTablaUsuarios tr').filter({ has: page.getByRole('cell', { name: ci, exact: true }) });
}

async function abrirAlta(page: Page, datos: DatosUsuario) {
  await page.locator('#btnCrear').click();
  const dialogo = page.locator('#dialogGestionarUsuario');
  await expect(dialogo).toBeVisible();
  await page.locator('#ci').fill(datos.ci);
  await page.locator('#nombre').fill(datos.nombre);
  await page.locator('#apellido').fill(datos.apellido);
  await page.locator('#contrasenia').fill(datos.clave);
  for (const rol of datos.roles) {
    await dialogo.locator(`input[name="roles[]"][value="${rol}"]`).check();
  }
}

async function guardar(page: Page) {
  await page.getByRole('button', { name: 'Guardar usuario' }).click();
  await page.waitForURL(/Administrador\.php\?(exito|error)=/);
}

async function abrirEdicion(page: Page, ci: string) {
  await filaDe(page, ci).locator('.btnEditar').click();
  await expect(page.locator('#dialogGestionarUsuario')).toBeVisible();
}

async function marcarRoles(page: Page, roles: string[]) {
  for (const casilla of await page.locator('#dialogGestionarUsuario input[name="roles[]"]').all()) {
    await casilla.setChecked(roles.includes(await casilla.getAttribute('value') ?? ''));
  }
}

async function desactivarDesdeTabla(page: Page, ci: string, aceptar = true) {
  page.once('dialog', dialogo => (aceptar ? dialogo.accept() : dialogo.dismiss()));
  await filaDe(page, ci).getByRole('button', { name: 'Desactivar' }).click();
}

/** Inicia sesión en un contexto nuevo y devuelve la URL a la que llegó. */
async function urlTrasLogin(browser: Browser, usuario: Usuario): Promise<{ url: string; error: string | null }> {
  const contexto = await browser.newContext({ baseURL: BASE_URL });
  const pagina = await contexto.newPage();
  await enviarLogin(pagina, usuario.ci, usuario.clave);
  await pagina.waitForLoadState();
  const url = pagina.url();
  const mensaje = pagina.locator('#errorMessage');
  const error = (await mensaje.count()) > 0 ? await mensaje.textContent() : null;
  await contexto.close();
  return { url, error };
}

async function puedeLoguearse(browser: Browser, usuario: Usuario, pagina = 'Docente.php') {
  const contexto = await browser.newContext({ baseURL: BASE_URL });
  const nuevaPagina = await contexto.newPage();
  await login(nuevaPagina, usuario);
  await expect(nuevaPagina).toHaveURL(new RegExp(`/${pagina.replace('.', '\\.')}$`));
  await contexto.close();
}

test.beforeEach(async ({ request }) => {
  creados = [];
  await loginApi(request, USUARIOS.principal);
  token = await tokenApi(request);
});

test.afterEach(async ({ request }) => {
  for (const ci of creados.filter(ci => /^\d{8}$/.test(ci))) {
    const respuesta = await request.delete(`${URL_API_USUARIOS}?id=${ci}`, { headers: { 'X-CSRF-Token': token } });
    expect([200, 404], `borrado de ${ci}`).toContain(respuesta.status());
  }
});

test.describe('Administrador de usuarios (coordinador)', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, USUARIOS.coordinador);
    await expect(page).toHaveURL(new RegExp(`${URL_ADMINISTRADOR}$`));
  });

  test('COO-01 alta de usuario válida', async ({ page, browser, request }) => {
    const datos = nuevoUsuario(['docente']);
    creados.push(datos.ci);

    await abrirAlta(page, datos);
    await guardar(page);

    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_CREADO);
    await expect(page.locator('#mensajeErrorAdministrador')).toHaveCount(0);
    const celdas = filaDe(page, datos.ci).locator('td');
    await expect(celdas.nth(1)).toHaveText(datos.nombre);
    await expect(celdas.nth(2)).toHaveText(datos.apellido);
    await expect(celdas.nth(3)).toHaveText('docente');
    await expect(celdas.nth(4)).toHaveText('Activo');

    expect((await obtenerUsuarioApi(request, datos.ci)).activo).toBe(true);
    await puedeLoguearse(browser, datos, 'Docente.php');
  });

  const altasInvalidas: { id: string; caso: string; cambios: Partial<DatosUsuario>; mensaje: string }[] = [
    { id: 'COO-02', caso: 'CI con letras', cambios: { ci: '9abc1234' }, mensaje: MENSAJE_FORMATO_CI },
    { id: 'COO-03', caso: 'CI de 7 dígitos', cambios: { ci: cedulaAleatoria().slice(0, 7) }, mensaje: MENSAJE_FORMATO_CI },
    { id: 'COO-05', caso: 'nombre vacío', cambios: { nombre: '' }, mensaje: MENSAJE_LARGO_NOMBRE },
    { id: 'COO-06', caso: 'nombre de 13 caracteres', cambios: { nombre: 'TEST_Nombre13' }, mensaje: MENSAJE_LARGO_NOMBRE },
    { id: 'COO-07', caso: 'apellido vacío', cambios: { apellido: '' }, mensaje: MENSAJE_LARGO_APELLIDO },
    { id: 'COO-08', caso: 'contraseña vacía', cambios: { clave: '' }, mensaje: 'El campo contraseña es obligatorio.' },
    { id: 'COO-09', caso: 'sin roles', cambios: { roles: [] }, mensaje: MENSAJE_SIN_ROL },
  ];

  for (const { id, caso, cambios, mensaje } of altasInvalidas) {
    test(`${id} alta con ${caso} muestra error y no crea el usuario`, async ({ page, request }) => {
      const datos = { ...nuevoUsuario(), ...cambios };
      creados.push(datos.ci);

      await abrirAlta(page, datos);
      await guardar(page);

      await expect(page.locator('#mensajeErrorAdministrador')).toHaveText(mensaje);
      await expect(page.locator('#mensajeExitoAdministrador')).toHaveCount(0);
      await expect(filaDe(page, datos.ci)).toHaveCount(0);
      if (/^\d{8}$/.test(datos.ci)) {
        expect(await obtenerUsuarioApi(request, datos.ci)).toBeNull();
      }
    });
  }

  test('COO-04 alta con CI duplicada muestra error y no cambia el usuario existente', async ({ page, request }) => {
    const existente = nuevoUsuario(['tecnico']);
    await crearUsuarioApi(request, existente);
    await page.reload();

    await abrirAlta(page, { ...existente, nombre: 'TEST_Otro', roles: ['coordinador'] });
    await guardar(page);

    await expect(page.locator('#mensajeErrorAdministrador')).toHaveText('El usuario ya existe');
    await expect(filaDe(page, existente.ci)).toHaveCount(1);
    const usuario = await obtenerUsuarioApi(request, existente.ci);
    expect(usuario.nombre).toBe(existente.nombre);
    expect(usuario.roles).toEqual(['tecnico']);
  });

  test('COO-10 editar nombre, apellido y roles actualiza la tabla', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await expect(page.locator('#ci')).toHaveValue(datos.ci);
    await expect(page.locator('#ci')).toHaveJSProperty('readOnly', true);
    await expect(page.locator('#nombre')).toHaveValue(datos.nombre);
    await expect(page.locator('#apellido')).toHaveValue(datos.apellido);
    await expect(page.locator('#contrasenia')).toHaveValue('');
    await expect(page.locator('input[name="roles[]"][value="docente"]')).toBeChecked();

    await page.locator('#nombre').fill('TEST_Editado');
    await page.locator('#apellido').fill('TEST_ApeEditado');
    await marcarRoles(page, ['coordinador', 'tecnico']);
    await guardar(page);

    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_ACTUALIZADO);
    const celdas = filaDe(page, datos.ci).locator('td');
    await expect(celdas.nth(1)).toHaveText('TEST_Editado');
    await expect(celdas.nth(2)).toHaveText('TEST_ApeEditado');
    await expect(celdas.nth(3)).toHaveText('coordinador, tecnico');
    await expect(celdas.nth(4)).toHaveText('Activo');
  });

  test('COO-11 editar la contraseña permite entrar con la nueva y no con la anterior', async ({ page, browser, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await page.locator('#contrasenia').fill('TEST_nueva2');
    await guardar(page);
    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_ACTUALIZADO);

    await puedeLoguearse(browser, { ci: datos.ci, clave: 'TEST_nueva2' });
    const anterior = await urlTrasLogin(browser, datos);
    expect(anterior.url).toContain('Login.php');
    expect(anterior.error).toBe(MENSAJE_CREDENCIALES);
  });

  test('COO-12 editar dejando la contraseña vacía mantiene la contraseña anterior', async ({ page, browser, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await page.locator('#apellido').fill('TEST_SinClave');
    await guardar(page);

    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_ACTUALIZADO);
    await expect(filaDe(page, datos.ci).locator('td').nth(2)).toHaveText('TEST_SinClave');
    await puedeLoguearse(browser, datos);
  });

  test('COO-13 editar con nombre vacío no deja al usuario sin nombre', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await page.locator('#nombre').fill('');
    await guardar(page);

    // procesarEditarUsuario.php trata el nombre vacío como "sin cambios" (comportamiento documentado).
    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_ACTUALIZADO);
    await expect(filaDe(page, datos.ci).locator('td').nth(1)).toHaveText(datos.nombre);
    expect((await obtenerUsuarioApi(request, datos.ci)).nombre).toBe(datos.nombre);
  });

  test('COO-14 editar con nombre de más de 12 caracteres muestra error', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await page.locator('#nombre').fill('TEST_Nombre13');
    await guardar(page);

    await expect(page.locator('#mensajeErrorAdministrador')).toHaveText(MENSAJE_LARGO_NOMBRE);
    await expect(filaDe(page, datos.ci).locator('td').nth(1)).toHaveText(datos.nombre);
  });

  test('COO-15 editar quitando todos los roles muestra error', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await abrirEdicion(page, datos.ci);
    await marcarRoles(page, []);
    await guardar(page);

    await expect(page.locator('#mensajeErrorAdministrador')).toHaveText(MENSAJE_SIN_ROL);
    expect((await obtenerUsuarioApi(request, datos.ci)).roles).toEqual(['docente']);
  });

  test('COO-16 desactivar un usuario lo deja inactivo y no puede iniciar sesión', async ({ page, browser, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    let textoConfirmacion = '';
    page.once('dialog', dialogo => { textoConfirmacion = dialogo.message(); });
    await desactivarDesdeTabla(page, datos.ci);
    await page.waitForURL(/exito=/);

    expect(textoConfirmacion).toBe('¿Está seguro de que desea desactivar este usuario?');
    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_DESACTIVADO);
    const fila = filaDe(page, datos.ci);
    await expect(fila.locator('td').nth(4)).toHaveText('Inactivo');
    await expect(fila.getByRole('button', { name: 'Activar' })).toBeVisible();
    expect((await obtenerUsuarioApi(request, datos.ci)).activo).toBe(false);

    const intento = await urlTrasLogin(browser, datos);
    expect(intento.url).toContain('Login.php');
    expect(intento.error).toBe(MENSAJE_INACTIVO);
  });

  test('COO-17 activar un usuario inactivo le permite iniciar sesión', async ({ page, browser, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    const baja = await request.put(`${URL_API_USUARIOS}?id=${datos.ci}`, {
      headers: { 'X-CSRF-Token': token }, data: { activo: false },
    });
    expect(baja.status()).toBe(200);
    await page.reload();

    const fila = filaDe(page, datos.ci);
    await expect(fila.locator('td').nth(4)).toHaveText('Inactivo');
    page.once('dialog', dialogo => dialogo.accept());
    await fila.getByRole('button', { name: 'Activar' }).click();
    await page.waitForURL(/exito=/);

    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_ACTIVADO);
    await expect(filaDe(page, datos.ci).locator('td').nth(4)).toHaveText('Activo');
    await puedeLoguearse(browser, datos);
  });

  test('COO-18 cancelar la confirmación de desactivar no lo desactiva', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    await desactivarDesdeTabla(page, datos.ci, false);

    await expect(page).toHaveURL(new RegExp(`${URL_ADMINISTRADOR}$`));
    await expect(filaDe(page, datos.ci).locator('td').nth(4)).toHaveText('Activo');
    expect((await obtenerUsuarioApi(request, datos.ci)).activo).toBe(true);
  });

  test('COO-20 nombre y apellido con HTML se muestran como texto', async ({ page, request }) => {
    const datos = { ...nuevoUsuario(['docente']), nombre: 'TEST_<b>x', apellido: 'TEST_<i>y</i>' };
    creados.push(datos.ci);

    await abrirAlta(page, datos);
    await guardar(page);

    await expect(page.locator('#mensajeExitoAdministrador')).toHaveText(MENSAJE_CREADO);
    const celdas = filaDe(page, datos.ci).locator('td');
    await expect(celdas.nth(1)).toHaveText(datos.nombre);
    await expect(celdas.nth(2)).toHaveText(datos.apellido);
    await expect(celdas.nth(1).locator('b')).toHaveCount(0);
    await expect(celdas.nth(2).locator('i')).toHaveCount(0);

    await abrirEdicion(page, datos.ci);
    await expect(page.locator('#nombre')).toHaveValue(datos.nombre);
    await expect(page.locator('#apellido')).toHaveValue(datos.apellido);
  });

  test('COO-21 alta con un rol inexistente enviado a mano muestra error', async ({ page, request }) => {
    const datos = nuevoUsuario([]);
    creados.push(datos.ci);

    await abrirAlta(page, datos);
    await page.locator('input[name="roles[]"][value="docente"]').evaluate((elemento: HTMLInputElement) => {
      elemento.value = 'superadmin';
      elemento.checked = true;
    });
    await guardar(page);

    await expect(page.locator('#mensajeErrorAdministrador')).toHaveText('Rol no válido: superadmin');
    expect(await obtenerUsuarioApi(request, datos.ci)).toBeNull();
  });

  test('COO-22 alta con roles enviados como texto y no como lista muestra error', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    creados.push(datos.ci);

    await abrirAlta(page, datos);
    await page.locator('input[name="roles[]"][value="docente"]')
      .evaluate((elemento: HTMLInputElement) => { elemento.name = 'roles'; });
    await page.getByRole('button', { name: 'Guardar usuario' }).click();

    // DEFECTO: procesarAltaUsuario.php:28 no verifica que roles sea un arreglo; crearUsuario(array $roles)
    // (AltaDatosUsuario.php:53) lanza TypeError, que no es Exception y no se captura (procesarAltaUsuario.php:68):
    // el coordinador ve un error fatal de PHP en lugar del mensaje. La API sí lo valida (ControladorUsuario.php:295).
    await expect(page).toHaveURL(/Administrador\.php\?error=/);
    await expect(page.locator('#mensajeErrorAdministrador')).toBeVisible();
    expect(await obtenerUsuarioApi(request, datos.ci)).toBeNull();
  });

  test('COO-23 desactivar una cédula inexistente no informa éxito', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);
    await page.reload();

    const inexistente = cedulaAleatoria();
    expect(await obtenerUsuarioApi(request, inexistente)).toBeNull();
    await filaDe(page, datos.ci).locator('input[name="cedula"]')
      .evaluate((elemento: HTMLInputElement, ci) => { elemento.value = ci; }, inexistente);
    await desactivarDesdeTabla(page, datos.ci);
    await page.waitForURL(/Administrador\.php\?(exito|error)=/);

    expect((await obtenerUsuarioApi(request, datos.ci)).activo).toBe(true);
    // DEFECTO: AltaDatosUsuario.php:183-187 devuelve el resultado de execute() sin mirar rowCount(), así que
    // procesarDesactivarUsuario.php:35-36 informa "Usuario desactivado exitosamente" aunque la cédula no exista.
    await expect(page.locator('#mensajeErrorAdministrador')).toBeVisible();
    await expect(page.locator('#mensajeExitoAdministrador')).toHaveCount(0);
  });

  test('COO-24 desactivar por POST sin token CSRF es rechazado', async ({ page, request }) => {
    const datos = nuevoUsuario(['docente']);
    await crearUsuarioApi(request, datos);

    // Simula un formulario de otro sitio que aprovecha la sesión abierta del coordinador.
    await page.request.post(`${URL_PAGINAS}/procesarDesactivarUsuario.php`, {
      form: { cedula: datos.ci },
      headers: { Origin: 'http://sitio-externo.example', Referer: 'http://sitio-externo.example/' },
    });

    // DEFECTO: procesarDesactivarUsuario.php (y alta, edición y activación) no piden token CSRF
    // (app/controlador/procesarDesactivarUsuario.php:15-21), a diferencia de la API (ControladorUsuario.php:54-56).
    expect((await obtenerUsuarioApi(request, datos.ci)).activo).toBe(true);
  });
});

test.describe('Coordinador sobre su propio usuario', () => {
  test('COO-19 el coordinador no puede desactivarse a sí mismo desde la tabla', async ({ page, request }) => {
    const coordinador = nuevoUsuario(['coordinador']);
    await crearUsuarioApi(request, coordinador);
    await login(page, coordinador);
    await expect(page).toHaveURL(new RegExp(`${URL_ADMINISTRADOR}$`));

    await desactivarDesdeTabla(page, coordinador.ci);
    await page.waitForURL(/Administrador\.php\?(exito|error)=/);

    // DEFECTO: procesarDesactivarUsuario.php:21-31 no compara la cédula con $_SESSION["cedula"];
    // la API sí lo impide con "No puede desactivar su propio usuario." (ControladorUsuario.php:230-233).
    await expect(page.locator('#mensajeErrorAdministrador')).toBeVisible();
    await expect(filaDe(page, coordinador.ci).locator('td').nth(4)).toHaveText('Activo');
    expect((await obtenerUsuarioApi(request, coordinador.ci)).activo).toBe(true);
  });
});
