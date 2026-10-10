import { test, expect, Page } from '@playwright/test';
import { USUARIOS, URL_PAGINAS, URL_API, MENSAJE_CREDENCIALES, APELLIDO_TEST, login, enviarLogin, loginApi, tokenApi, cedulaAleatoria } from './helpers';

const MENSAJE_SIN_SESION = 'Debe iniciar sesión para acceder a esa página.';
const MENSAJE_SIN_AUTORIZACION = 'No tiene autorización para acceder a ese panel.';

const PAGINAS_PROTEGIDAS = [
  'Administrador.php', 'Tecnico.php', 'Docente.php', 'PanelRoles.php',
  'IngresoDeTickets.php', 'VistaDeTickets.php', 'Equipos.php', 'Prestamos.php',
  'SolicitarLab.php', 'VistaLab.php', 'HistorialTecnico.php', 'RegistrarDiagnostico.php',
  'ConsultarDiagnostico.php', 'ModificarDiagnostico.php', 'RegistrarSolucion.php', 'RegistrarReparacion.php',
];

const PROCESAR_PROTEGIDOS = [
  'procesarAltaUsuario.php', 'procesarEditarUsuario.php', 'procesarActivarUsuario.php',
  'procesarDesactivarUsuario.php', 'procesarSolicitudLaboratorio.php',
];

const PAGINAS_TECNICO = [
  'Tecnico.php', 'VistaDeTickets.php', 'Equipos.php', 'Prestamos.php', 'VistaLab.php', 'HistorialTecnico.php',
  'RegistrarDiagnostico.php', 'ConsultarDiagnostico.php', 'ModificarDiagnostico.php', 'RegistrarSolucion.php', 'RegistrarReparacion.php',
];
const PAGINAS_DOCENTE = ['Docente.php', 'IngresoDeTickets.php', 'SolicitarLab.php'];

async function verificarRedireccionLogin(page: Page, pagina: string, mensaje: string) {
  await page.goto(`${URL_PAGINAS}/${pagina}`);
  await expect(page, pagina).toHaveURL(/\/Login\.php\?error=/);
  await expect(page.locator('#errorMessage'), pagina).toHaveText(mensaje);
}

async function cookieDeSesion(page: Page) {
  const cookies = await page.context().cookies();
  return cookies.find(cookie => cookie.name === 'PHPSESSID');
}

test.describe('Seguridad: acceso sin sesión', () => {
  PAGINAS_PROTEGIDAS.forEach((pagina, i) => {
    test(`SEG-01.${String(i + 1).padStart(2, '0')} sin sesión ${pagina} vuelve al login con mensaje`, async ({ page }) => {
      await verificarRedireccionLogin(page, pagina, MENSAJE_SIN_SESION);
    });
  });

  PROCESAR_PROTEGIDOS.forEach((pagina, i) => {
    test(`SEG-02.${i + 1} sin sesión ${pagina} por GET vuelve al login con mensaje`, async ({ page }) => {
      await verificarRedireccionLogin(page, pagina, MENSAJE_SIN_SESION);
    });

    test(`SEG-03.${i + 1} sin sesión ${pagina} por POST vuelve al login con mensaje`, async ({ request }) => {
      const respuesta = await request.post(`${URL_PAGINAS}/${pagina}`, {
        form: { ci: '90000009', cedula: '90000009', nombre: 'TEST_x', apellido: APELLIDO_TEST, contrasenia: 'x', idLaboratorio: '1' },
      });

      expect(respuesta.url()).toMatch(/\/Login\.php\?error=/);
      expect(await respuesta.text()).toContain(MENSAJE_SIN_SESION);
    });
  });

  test('SEG-04 procesarLogin.php por GET vuelve al login sin iniciar sesión', async ({ page }) => {
    await page.goto(`${URL_PAGINAS}/procesarLogin.php`);
    await expect(page).toHaveURL(/\/Login\.php$/);

    await verificarRedireccionLogin(page, 'Docente.php', MENSAJE_SIN_SESION);
  });
});

test.describe('Seguridad: acceso por rol', () => {
  test('SEG-05 docente no accede a Administrador.php ni Tecnico.php', async ({ page }) => {
    await login(page, USUARIOS.docente);

    await verificarRedireccionLogin(page, 'Administrador.php', MENSAJE_SIN_AUTORIZACION);
    await verificarRedireccionLogin(page, 'Tecnico.php', MENSAJE_SIN_AUTORIZACION);
  });

  test('SEG-06 docente no accede a ninguna página de técnico', async ({ page }) => {
    await login(page, USUARIOS.docente);

    for (const pagina of PAGINAS_TECNICO) {
      await verificarRedireccionLogin(page, pagina, MENSAJE_SIN_AUTORIZACION);
    }
  });

  test('SEG-07 técnico no accede a Administrador.php ni IngresoDeTickets.php', async ({ page }) => {
    await login(page, USUARIOS.tecnico);

    await verificarRedireccionLogin(page, 'Administrador.php', MENSAJE_SIN_AUTORIZACION);
    await verificarRedireccionLogin(page, 'IngresoDeTickets.php', MENSAJE_SIN_AUTORIZACION);
  });

  test('SEG-08 técnico no accede a las páginas de docente', async ({ page }) => {
    await login(page, USUARIOS.tecnico);

    for (const pagina of PAGINAS_DOCENTE) {
      await verificarRedireccionLogin(page, pagina, MENSAJE_SIN_AUTORIZACION);
    }
  });

  test('SEG-09 coordinador solo no accede a páginas de técnico ni de docente', async ({ page }) => {
    await login(page, USUARIOS.coordinador);

    for (const pagina of [...PAGINAS_TECNICO, ...PAGINAS_DOCENTE]) {
      await verificarRedireccionLogin(page, pagina, MENSAJE_SIN_AUTORIZACION);
    }
  });

  test('SEG-10 docente no puede usar procesarDesactivarUsuario.php', async ({ page }) => {
    await login(page, USUARIOS.docente);

    const respuesta = await page.request.post(`${URL_PAGINAS}/procesarDesactivarUsuario.php`, {
      form: { cedula: USUARIOS.coordinador.ci },
    });

    expect(respuesta.url()).toMatch(/\/Login\.php\?error=/);
    expect(await respuesta.text()).toContain(MENSAJE_SIN_AUTORIZACION);
  });
});

test.describe('Seguridad: sesión', () => {
  test('SEG-11 cerrar sesión y volver atrás manda al login', async ({ page }) => {
    await login(page, USUARIOS.docente);
    await expect(page).toHaveURL(/\/Docente\.php$/);

    await page.getByRole('link', { name: 'Cerrar sesion' }).click();
    await expect(page).toHaveURL(/\/Login\.php$/);

    await page.goBack();
    await expect(page).toHaveURL(/\/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_SIN_SESION);
    await expect(page.locator('section.encabezado')).toHaveCount(0);
  });

  test('SEG-12 después de cerrar sesión entrar a la página protegida manda al login', async ({ page }) => {
    await login(page, USUARIOS.tecnico);
    await page.goto(`${URL_PAGINAS}/VistaDeTickets.php`);
    await expect(page).toHaveURL(/\/VistaDeTickets\.php$/);

    await page.goto(`${URL_PAGINAS}/cerrarSesion.php`);
    await expect(page).toHaveURL(/\/Login\.php$/);

    await verificarRedireccionLogin(page, 'VistaDeTickets.php', MENSAJE_SIN_SESION);
    await verificarRedireccionLogin(page, 'Tecnico.php', MENSAJE_SIN_SESION);
  });

  test('SEG-13 la cookie de sesión anterior no sirve después de cerrar sesión', async ({ page }) => {
    await login(page, USUARIOS.docente);
    const cookieAnterior = await cookieDeSesion(page);
    expect(cookieAnterior).toBeTruthy();

    await page.goto(`${URL_PAGINAS}/cerrarSesion.php`);
    await page.context().clearCookies();
    await page.context().addCookies([cookieAnterior!]);

    await verificarRedireccionLogin(page, 'Docente.php', MENSAJE_SIN_SESION);
  });

  test('SEG-14 el id de sesión cambia al iniciar sesión', async ({ page }) => {
    await page.goto(`${URL_PAGINAS}/Login.php`);
    const antes = await cookieDeSesion(page);
    expect(antes).toBeTruthy();

    await login(page, USUARIOS.docente);
    const despues = await cookieDeSesion(page);

    expect(despues?.value).toBeTruthy();
    expect(despues?.value).not.toBe(antes?.value);
  });

  // DEFECTO: Login::autenticar revisa si el usuario está activo antes de verificar la clave
  // (app/modelo/Login.php:29-31), así con cualquier clave se sabe que la cédula existe y está inactiva.
  test('SEG-15 usuario inactivo con clave incorrecta no revela que existe', async ({ page }) => {
    await enviarLogin(page, USUARIOS.inactivo.ci, 'TEST_claveIncorrecta');

    await expect(page).toHaveURL(/Login\.php\?error=/);
    await expect(page.locator('#errorMessage')).toHaveText(MENSAJE_CREDENCIALES);
  });

  // DEFECTO: los formularios procesar*Usuario.php no piden token CSRF (app/controlador/procesarAltaUsuario.php:15-50,
  // app/vista/administrador.php:74 y 101), a diferencia de la API (app/controlador/ControladorUsuario.php:54-55).
  // Un POST sin token con la sesión del coordinador crea el usuario.
  test('SEG-16 alta de usuario por formulario sin token CSRF se rechaza', async ({ page, playwright }) => {
    const cedula = cedulaAleatoria();
    await login(page, USUARIOS.coordinador);

    try {
      await page.request.post(`${URL_PAGINAS}/procesarAltaUsuario.php`, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: `ci=${cedula}&nombre=TEST_Csrf&apellido=${APELLIDO_TEST}&contrasenia=TEST_clave1&roles%5B%5D=docente`,
      });

      const consulta = await page.request.get(`${URL_API}/usuarios.php?id=${cedula}`);
      expect(consulta.status(), 'el usuario no debería haberse creado').toBe(404);
    } finally {
      const contexto = await playwright.request.newContext({ baseURL: 'http://localhost:3000' });
      await loginApi(contexto, USUARIOS.principal);
      const existente = await contexto.get(`${URL_API}/usuarios.php?id=${cedula}`);
      if (existente.status() === 200) {
        const token = await tokenApi(contexto);
        const borrado = await contexto.delete(`${URL_API}/usuarios.php?id=${cedula}`, { headers: { 'X-CSRF-Token': token } });
        expect(borrado.ok(), 'limpieza del usuario TEST_Csrf').toBe(true);
      }
      await contexto.dispose();
    }
  });
});
