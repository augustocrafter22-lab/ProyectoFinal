import { test, expect, Page, APIRequestContext } from '@playwright/test';
import { USUARIOS, URL_PAGINAS, URL_API, login, loginApi, tokenApi, tokenDePagina } from './helpers';

const URL_DOCENTE = `${URL_PAGINAS}/Docente.php`;
const URL_INGRESO_TICKETS = `${URL_PAGINAS}/IngresoDeTickets.php`;
const URL_SOLICITAR_LAB = `${URL_PAGINAS}/SolicitarLab.php`;
const URL_PROCESAR_SOLICITUD = `${URL_PAGINAS}/procesarSolicitudLaboratorio.php`;
const URL_API_TICKETS = `${URL_API}/tickets.php`;
const URL_API_SOLICITUDES = `${URL_API}/solicitudes.php`;

const MENSAJE_ERROR_BD = 'Ocurrió un error, intente nuevamente.';
const MENSAJE_SOLICITUD_OK = 'Solicitud enviada correctamente';

/** Fecha local en formato AAAA-MM-DD desplazada la cantidad de días indicada desde hoy. */
function fechaRelativa(dias: number): string {
  const fecha = new Date();
  fecha.setDate(fecha.getDate() + dias);
  const mes = String(fecha.getMonth() + 1).padStart(2, '0');
  const dia = String(fecha.getDate()).padStart(2, '0');
  return `${fecha.getFullYear()}-${mes}-${dia}`;
}

function marcaUnica(prefijo: string): string {
  return `TEST_${prefijo}_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
}

/** Contexto de API logueado como el usuario principal (coordinador) para consultar y borrar lo creado. */
async function prepararPrincipal(request: APIRequestContext): Promise<string> {
  await loginApi(request, USUARIOS.principal);
  return tokenApi(request);
}

async function borrarTicket(request: APIRequestContext, idTicket: string) {
  const token = await prepararPrincipal(request);
  const borrado = await request.delete(`${URL_API_TICKETS}?id=${idTicket}`, { headers: { 'X-CSRF-Token': token } });
  expect(borrado.status(), `borrado del ticket ${idTicket}`).toBe(200);
}

/** Busca las solicitudes del docente cuyo detalle es la marca indicada. */
async function buscarSolicitudes(request: APIRequestContext, marca: string): Promise<any[]> {
  await prepararPrincipal(request);
  const respuesta = await request.get(URL_API_SOLICITUDES);
  expect(respuesta.status()).toBe(200);
  const cuerpo = await respuesta.json();
  return cuerpo.datos.filter((s: any) => s.detalle === marca && s.cedulaSolicitante === USUARIOS.docente.ci);
}

/** Borra todas las solicitudes con la marca indicada (si las hay) y devuelve las que encontró. */
async function limpiarSolicitudes(request: APIRequestContext, marca: string): Promise<any[]> {
  const encontradas = await buscarSolicitudes(request, marca);
  const token = await tokenApi(request);
  for (const solicitud of encontradas) {
    const borrado = await request.delete(`${URL_API_SOLICITUDES}?id=${solicitud.idSolicitud}`, {
      headers: { 'X-CSRF-Token': token },
    });
    expect(borrado.status(), `borrado de la solicitud ${solicitud.idSolicitud}`).toBe(200);
  }
  return encontradas;
}

async function abrirIngresoTickets(page: Page) {
  await page.goto(URL_INGRESO_TICKETS);
  await expect(page.locator('#ticketForm')).toBeVisible();
  // Los equipos se cargan con fetch, se espera a que haya al menos uno además de la opción por defecto.
  await expect(page.locator('#equipo option')).not.toHaveCount(1);
}

async function completarTicket(page: Page, asunto: string, grupo = '3BD') {
  await page.locator('#laboratorioTaller').selectOption({ index: 1 });
  await page.locator('#equipo').selectOption({ index: 1 });
  await page.locator('#asunto').fill(asunto);
  await page.locator('#descripcion').fill('TEST_ ticket creado por los tests de Playwright.');
  await page.locator('#turno').selectOption('Matutino');
  await page.locator('#grupo').fill(grupo);
  await page.locator('#profesor').fill('TEST_Profesor');
}

async function enviarTicket(page: Page) {
  const respuestaPromesa = page.waitForResponse(r => r.url().includes(URL_API_TICKETS) && r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Enviar Ticket' }).click();
  return respuestaPromesa;
}

async function abrirSolicitarLab(page: Page) {
  await page.goto(URL_SOLICITAR_LAB);
  await expect(page.locator('#LabForm')).toBeVisible();
}

/** Primer laboratorio del select de la página de solicitud. */
async function primerLaboratorio(page: Page): Promise<string> {
  const valor = await page.locator('#laboratorioSolicitud option').nth(1).getAttribute('value');
  expect(valor, 'debería haber al menos un laboratorio').toBeTruthy();
  return valor as string;
}

/**
 * Envía el formulario de solicitud directo al servidor (sin las validaciones del navegador)
 * con la sesión de la página y devuelve los mensajes de la redirección.
 */
async function enviarSolicitudDirecta(page: Page, campos: Record<string, string>) {
  const respuesta = await page.request.post(URL_PROCESAR_SOLICITUD, { form: campos });
  const url = new URL(respuesta.url());
  return { exito: url.searchParams.get('exito'), error: url.searchParams.get('error') };
}

test.describe('Panel docente', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, USUARIOS.docente);
  });

  test('DOC-01 el panel muestra la bienvenida y las opciones del docente', async ({ page }) => {
    await expect(page).toHaveURL(new RegExp(`${URL_DOCENTE}$`));
    await expect(page.locator('section.encabezado h1')).toHaveText('Bienvenido, TEST_Docente TEST_Playwright (Docente)');
    await expect(page.locator('section.modulo-imagen p')).toHaveText('Haga uso del menu para comenzar.');

    const menu = page.locator('.listaNavegacion');
    await expect(menu.getByRole('link', { name: 'Solicitar laboratorio' })).toBeVisible();
    await expect(menu.getByRole('link', { name: 'Ingresar Ticket' })).toBeVisible();
    await expect(menu.getByRole('link', { name: 'Cerrar sesion' })).toBeVisible();
  });

  test('DOC-02 el enlace Ingresar Ticket lleva al ingreso de tickets', async ({ page }) => {
    await page.locator('.listaNavegacion').getByRole('link', { name: 'Ingresar Ticket' }).click();
    await expect(page).toHaveURL(new RegExp(`${URL_INGRESO_TICKETS}$`));
    await expect(page.locator('section.encabezado h1')).toHaveText('Nuevo Ticket');
    await expect(page.locator('#ticketForm')).toBeVisible();
  });

  test('DOC-03 el enlace Solicitar laboratorio lleva a la solicitud de laboratorio', async ({ page }) => {
    await page.locator('.listaNavegacion').getByRole('link', { name: 'Solicitar laboratorio' }).click();
    await expect(page).toHaveURL(new RegExp(`${URL_SOLICITAR_LAB}$`));
    await expect(page.locator('section.encabezado h1')).toHaveText('Solicitud de Laboratorio');
    await expect(page.locator('#LabForm')).toBeVisible();
  });
});

test.describe('Docente: ingreso de tickets', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, USUARIOS.docente);
    await abrirIngresoTickets(page);
  });

  test('DOC-04 ingresar ticket correcto', async ({ page, request }) => {
    const asunto = marcaUnica('ticket');
    await completarTicket(page, asunto);

    const respuesta = await enviarTicket(page);
    expect(respuesta.status()).toBe(201);
    const cuerpo = await respuesta.json();
    expect(cuerpo.exito).toBe(true);
    expect(cuerpo.datos.asunto).toBe(asunto);
    expect(cuerpo.datos.estado).toBe('Pendiente');
    const idTicket = cuerpo.datos.idTicket;

    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText(`Ticket ${idTicket} registrado correctamente.`);
    await expect(mensaje).toHaveClass('mensaje-exito');
    await expect(page.locator('#asunto')).toHaveValue('');
    await expect(page.locator('#laboratorioTaller')).toHaveValue('');

    // El ticket quedó guardado: se consulta con el usuario principal, porque el docente no puede listar tickets.
    await prepararPrincipal(request);
    const consulta = await request.get(`${URL_API_TICKETS}?id=${idTicket}`);
    expect(consulta.status()).toBe(200);
    expect((await consulta.json()).datos.asunto).toBe(asunto);

    await borrarTicket(request, idTicket);
    expect((await request.get(`${URL_API_TICKETS}?id=${idTicket}`)).status()).toBe(404);
  });

  test('DOC-05 ingresar ticket con campos vacíos no envía el formulario', async ({ page }) => {
    let seEnvioTicket = false;
    page.on('request', r => {
      if (r.url().includes(URL_API_TICKETS) && r.method() === 'POST') {
        seEnvioTicket = true;
      }
    });

    await page.getByRole('button', { name: 'Enviar Ticket' }).click();

    const camposObligatorios = ['#laboratorioTaller', '#equipo', '#asunto', '#descripcion', '#turno', '#grupo', '#profesor'];
    for (const campo of camposObligatorios) {
      const estaVacio = await page.locator(campo).evaluate((elemento: HTMLInputElement) => elemento.validity.valueMissing);
      expect(estaVacio, `${campo} debería marcarse como obligatorio`).toBe(true);
    }

    await expect(page.locator('#mensajeIngresoTickets')).toHaveText('');
    expect(seEnvioTicket).toBe(false);
  });

  test('DOC-06 ingresar ticket con campos solo con espacios muestra el error del servidor', async ({ page }) => {
    // El navegador acepta los espacios como valor, pero el JS hace trim y el servidor lo rechaza.
    await completarTicket(page, '   ');

    const respuesta = await enviarTicket(page);
    expect(respuesta.status()).toBe(400);
    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText('El campo asunto debe tener entre 1 y 100 caracteres.');
    await expect(mensaje).toHaveClass('mensaje-error');
  });

  test('DOC-07 la API rechaza un ticket con campos vacíos', async ({ page }) => {
    const token = await tokenDePagina(page);

    const respuesta = await page.request.post(URL_API_TICKETS, {
      headers: { 'X-CSRF-Token': token },
      data: { laboratorio: '', equipo: '', asunto: '', descripcion: '', turno: '', grupo: '', profesor: '' },
    });

    expect(respuesta.status()).toBe(400);
    const cuerpo = await respuesta.json();
    expect(cuerpo.exito).toBe(false);
    expect(cuerpo.mensaje).toBe('El campo laboratorio debe tener entre 1 y 20 caracteres.');
  });

  test('DOC-08 ingresar ticket con asunto de 100 caracteres se acepta', async ({ page, request }) => {
    const marca = marcaUnica('asunto');
    const asunto = marca + 'x'.repeat(100 - marca.length);
    await completarTicket(page, asunto);

    const respuesta = await enviarTicket(page);
    expect(respuesta.status()).toBe(201);
    const cuerpo = await respuesta.json();
    expect(cuerpo.datos.asunto).toBe(asunto);
    await expect(page.locator('#mensajeIngresoTickets')).toHaveClass('mensaje-exito');

    await borrarTicket(request, cuerpo.datos.idTicket);
  });

  test('DOC-09 ingresar ticket con asunto de más de 100 caracteres muestra error', async ({ page }) => {
    const marca = marcaUnica('asunto');
    await completarTicket(page, marca + 'x'.repeat(101 - marca.length));

    const respuesta = await enviarTicket(page);
    expect(respuesta.status()).toBe(400);
    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText('El campo asunto debe tener entre 1 y 100 caracteres.');
    await expect(mensaje).toHaveClass('mensaje-error');
  });

  test('DOC-10 ingresar ticket con grupo de más de 10 caracteres muestra error', async ({ page }) => {
    await completarTicket(page, marcaUnica('grupo'), 'TEST_GRUPO1');

    const respuesta = await enviarTicket(page);
    expect(respuesta.status()).toBe(400);
    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText('El campo grupo debe tener entre 1 y 10 caracteres.');
    await expect(mensaje).toHaveClass('mensaje-error');
  });

  test('DOC-11 sin conexión a la base de datos muestra error y no limpia el formulario', async ({ page }) => {
    // Se simula la respuesta que da ControladorTicket cuando ConectorPDO no logra conectarse.
    await page.route(`**${URL_API_TICKETS}`, async route => {
      if (route.request().method() !== 'POST') {
        return route.continue();
      }
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ exito: false, mensaje: MENSAJE_ERROR_BD, datos: null }),
      });
    });

    const asunto = marcaUnica('sinBD');
    await completarTicket(page, asunto);
    await page.getByRole('button', { name: 'Enviar Ticket' }).click();

    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText(MENSAJE_ERROR_BD);
    await expect(mensaje).toHaveClass('mensaje-error');
    await expect(page.locator('#asunto')).toHaveValue(asunto);
  });

  test('DOC-12 sin conexión con el servidor muestra error', async ({ page }) => {
    await page.route(`**${URL_API_TICKETS}`, route => route.abort('connectionrefused'));

    await completarTicket(page, marcaUnica('sinServidor'));
    await page.getByRole('button', { name: 'Enviar Ticket' }).click();

    const mensaje = page.locator('#mensajeIngresoTickets');
    await expect(mensaje).toHaveText('No se pudo conectar con el servidor.');
    await expect(mensaje).toHaveClass('mensaje-error');
  });
});

test.describe('Docente: solicitar laboratorio', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, USUARIOS.docente);
    await abrirSolicitarLab(page);
  });

  test('DOC-13 solicitar laboratorio correctamente', async ({ page, request }) => {
    const marca = marcaUnica('solicitud');
    const fecha = fechaRelativa(30);
    const laboratorio = await primerLaboratorio(page);

    try {
      await page.locator('#laboratorioSolicitud').selectOption(laboratorio);
      await page.locator('#SolicitudDeSoftware').selectOption('Si');
      await page.locator('#DetalleSoftware').fill(marca);
      await page.locator('#Restricciones').fill('TEST_ sin restricciones');
      await page.locator('#FechaEstimada').fill(fecha);
      await page.locator('#HoraEstimada').fill('10:30');
      await page.getByRole('button', { name: 'Publicar Solicitud' }).click();

      await expect(page).toHaveURL(/SolicitarLab\.php\?exito=/);
      await expect(page.locator('#mensajeExitoSolicitarLab')).toHaveText(MENSAJE_SOLICITUD_OK);
      await expect(page.locator('#mensajeErrorSolicitarLab')).toHaveCount(0);

      const registradas = await buscarSolicitudes(request, marca);
      expect(registradas).toHaveLength(1);
      expect(registradas[0]).toMatchObject({
        idLaboratorio: laboratorio,
        solicitaSoftware: true,
        restricciones: 'TEST_ sin restricciones',
        fechaEstimada: fecha,
        horaEstimada: '10:30:00',
      });
    } finally {
      await limpiarSolicitudes(request, marca);
    }
    expect(await buscarSolicitudes(request, marca)).toHaveLength(0);
  });

  test('DOC-14 solicitar laboratorio con campos vacíos no envía el formulario', async ({ page }) => {
    let seEnvioSolicitud = false;
    page.on('request', r => {
      if (r.url().includes(URL_PROCESAR_SOLICITUD)) {
        seEnvioSolicitud = true;
      }
    });

    await page.getByRole('button', { name: 'Publicar Solicitud' }).click();

    for (const campo of ['#laboratorioSolicitud', '#FechaEstimada', '#HoraEstimada']) {
      const estaVacio = await page.locator(campo).evaluate((elemento: HTMLInputElement) => elemento.validity.valueMissing);
      expect(estaVacio, `${campo} debería marcarse como obligatorio`).toBe(true);
    }
    await expect(page).toHaveURL(new RegExp(`${URL_SOLICITAR_LAB}$`));
    expect(seEnvioSolicitud).toBe(false);
  });

  test('DOC-15 con software sin detalle el navegador y el servidor lo rechazan', async ({ page }) => {
    await page.locator('#laboratorioSolicitud').selectOption({ index: 1 });
    await page.locator('#SolicitudDeSoftware').selectOption('Si');
    await page.locator('#FechaEstimada').fill(fechaRelativa(30));
    await page.locator('#HoraEstimada').fill('10:30');
    await page.getByRole('button', { name: 'Publicar Solicitud' }).click();

    const detalleVacio = await page.locator('#DetalleSoftware').evaluate((elemento: HTMLTextAreaElement) => elemento.validity.valueMissing);
    expect(detalleVacio).toBe(true);
    await expect(page).toHaveURL(new RegExp(`${URL_SOLICITAR_LAB}$`));

    const resultado = await enviarSolicitudDirecta(page, {
      idLaboratorio: await primerLaboratorio(page),
      solicitaSoftware: 'Si',
      detalle: '   ',
      restricciones: '',
      fechaEstimada: fechaRelativa(30),
      horaEstimada: '10:30',
    });
    expect(resultado.exito).toBeNull();
    expect(resultado.error).toBe('El campo detalle del software es obligatorio.');
  });

  test('DOC-16 el navegador no permite elegir una fecha pasada', async ({ page }) => {
    await expect(page.locator('#FechaEstimada')).toHaveAttribute('min', fechaRelativa(0));

    await page.locator('#laboratorioSolicitud').selectOption({ index: 1 });
    await page.locator('#FechaEstimada').fill(fechaRelativa(-1));
    await page.locator('#HoraEstimada').fill('10:30');
    await page.getByRole('button', { name: 'Publicar Solicitud' }).click();

    const fechaAnterior = await page.locator('#FechaEstimada').evaluate((elemento: HTMLInputElement) => elemento.validity.rangeUnderflow);
    expect(fechaAnterior).toBe(true);
    await expect(page).toHaveURL(new RegExp(`${URL_SOLICITAR_LAB}$`));
  });

  test('DOC-17 el navegador no permite una hora ya pasada en el día de hoy', async ({ page }) => {
    const ahora = new Date();
    test.skip(ahora.getHours() === 0 && ahora.getMinutes() === 0, 'a las 00:00 no hay hora pasada en el día');

    await page.locator('#laboratorioSolicitud').selectOption({ index: 1 });
    await page.locator('#FechaEstimada').fill(fechaRelativa(0));
    await page.locator('#HoraEstimada').fill('00:00');
    await page.getByRole('button', { name: 'Publicar Solicitud' }).click();

    const horaAnterior = await page.locator('#HoraEstimada').evaluate((elemento: HTMLInputElement) => elemento.validity.rangeUnderflow);
    expect(horaAnterior).toBe(true);
    await expect(page).toHaveURL(new RegExp(`${URL_SOLICITAR_LAB}$`));
  });

  test('DOC-18 el servidor rechaza una solicitud con fecha pasada', async ({ page, request }) => {
    const marca = marcaUnica('fechaPasada');
    try {
      const resultado = await enviarSolicitudDirecta(page, {
        idLaboratorio: await primerLaboratorio(page),
        solicitaSoftware: 'Si',
        detalle: marca,
        restricciones: '',
        fechaEstimada: fechaRelativa(-10),
        horaEstimada: '10:30',
      });
      // DEFECTO: procesarSolicitudLaboratorio.php:23 solo verifica que la fecha no esté vacía y la registra aunque sea pasada; el control existe solo en el navegador (min en Laboratorio.js:20).
      expect(resultado.exito).toBeNull();
      expect(resultado.error).toBeTruthy();
    } finally {
      await limpiarSolicitudes(request, marca);
    }
  });

  const fechasInvalidas = [
    { id: 'DOC-19', caso: 'fecha inexistente', valor: '2026-02-31' },
    { id: 'DOC-20', caso: 'fecha con formato incorrecto', valor: '31/12/2026' },
  ];
  for (const { id, caso, valor } of fechasInvalidas) {
    test(`${id} el servidor rechaza una solicitud con ${caso} (${valor})`, async ({ page, request }) => {
      const marca = marcaUnica('fecha');
      try {
        const resultado = await enviarSolicitudDirecta(page, {
          idLaboratorio: await primerLaboratorio(page),
          solicitaSoftware: 'Si',
          detalle: marca,
          restricciones: '',
          fechaEstimada: valor,
          horaEstimada: '10:30',
        });
        // DEFECTO: procesarSolicitudLaboratorio.php:23 solo usa Validador::requerido y no Validador::fecha (Validador.php:109); responde "Solicitud enviada correctamente" y MySQL guarda la fecha como 0000-00-00.
        expect(resultado.exito).toBeNull();
        expect(resultado.error).toContain('fecha');
        expect(await buscarSolicitudes(request, marca)).toHaveLength(0);
      } finally {
        await limpiarSolicitudes(request, marca);
      }
    });
  }

  const horasInvalidas = [
    { id: 'DOC-21', caso: 'hora con formato incorrecto', valor: '10h30' },
    { id: 'DOC-22', caso: 'hora fuera de rango', valor: '25:00' },
  ];
  for (const { id, caso, valor } of horasInvalidas) {
    test(`${id} el servidor rechaza una solicitud con ${caso} (${valor})`, async ({ page, request }) => {
      const marca = marcaUnica('hora');
      try {
        const resultado = await enviarSolicitudDirecta(page, {
          idLaboratorio: await primerLaboratorio(page),
          solicitaSoftware: 'Si',
          detalle: marca,
          restricciones: '',
          fechaEstimada: fechaRelativa(30),
          horaEstimada: valor,
        });
        // DEFECTO: procesarSolicitudLaboratorio.php:24 solo usa Validador::requerido y no Validador::hora (Validador.php:128); responde éxito y MySQL guarda '25:00' como 25:00:00 y '10h30' como 00:00:10.
        expect(resultado.exito).toBeNull();
        expect(resultado.error).toContain('hora');
        expect(await buscarSolicitudes(request, marca)).toHaveLength(0);
      } finally {
        await limpiarSolicitudes(request, marca);
      }
    });
  }

  test('DOC-23 la API de solicitudes rechaza fechas y horas inválidas', async ({ page, request }) => {
    // La API también acepta el alta de solicitudes del docente; se usa el token de IngresoDeTickets.
    await page.goto(URL_INGRESO_TICKETS);
    const token = await tokenDePagina(page);
    await abrirSolicitarLab(page);
    const laboratorio = await primerLaboratorio(page);
    const marca = marcaUnica('api');

    const casos = [
      { fechaEstimada: '2026-02-31', horaEstimada: '10:30', mensaje: 'El campo fechaEstimada debe ser una fecha válida con formato AAAA-MM-DD.' },
      { fechaEstimada: '31/12/2026', horaEstimada: '10:30', mensaje: 'El campo fechaEstimada debe ser una fecha válida con formato AAAA-MM-DD.' },
      { fechaEstimada: fechaRelativa(30), horaEstimada: '25:00', mensaje: 'El campo horaEstimada debe ser una hora válida con formato HH:MM.' },
      { fechaEstimada: fechaRelativa(30), horaEstimada: '10h30', mensaje: 'El campo horaEstimada debe ser una hora válida con formato HH:MM.' },
    ];
    try {
      for (const caso of casos) {
        const respuesta = await page.request.post(URL_API_SOLICITUDES, {
          headers: { 'X-CSRF-Token': token },
          data: { idLaboratorio: laboratorio, solicitaSoftware: true, detalle: marca, fechaEstimada: caso.fechaEstimada, horaEstimada: caso.horaEstimada },
        });
        expect(respuesta.status(), `${caso.fechaEstimada} ${caso.horaEstimada}`).toBe(400);
        expect((await respuesta.json()).mensaje).toBe(caso.mensaje);
      }
    } finally {
      await limpiarSolicitudes(request, marca);
    }
  });

  test('DOC-24 la API de solicitudes rechaza una fecha pasada', async ({ page, request }) => {
    await page.goto(URL_INGRESO_TICKETS);
    const token = await tokenDePagina(page);
    await abrirSolicitarLab(page);
    const marca = marcaUnica('apiPasada');

    try {
      const respuesta = await page.request.post(URL_API_SOLICITUDES, {
        headers: { 'X-CSRF-Token': token },
        data: { idLaboratorio: await primerLaboratorio(page), solicitaSoftware: true, detalle: marca, fechaEstimada: fechaRelativa(-10), horaEstimada: '10:30' },
      });
      // DEFECTO: ControladorSolicitudLaboratorio.php:292 solo valida que la fecha exista con Validador::fecha; acepta fechas pasadas.
      expect(respuesta.status()).toBe(400);
    } finally {
      await limpiarSolicitudes(request, marca);
    }
  });
});
