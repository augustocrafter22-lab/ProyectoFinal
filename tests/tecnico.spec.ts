import { test, expect, Page, APIRequestContext, Locator } from '@playwright/test';
import { USUARIOS, URL_PAGINAS, URL_API, login, loginApi, tokenApi } from './helpers';

const LAB_1 = { id: 'LAB001', numero: 'Lab 1' };
const LAB_2 = { id: 'LAB002', numero: 'Lab 2' };
const CI_TECNICO = USUARIOS.tecnico.ci;
const CI_DOCENTE = USUARIOS.docente.ci;

type Equipo = { idEquipo: string; idLaboratorio: string; marca: string; estado: string; disponibilidad: string; informacion: string };
type Ticket = { idTicket: string; asunto: string; equipo: string; estado: string; prioridad: string; fechaCreacion: string; fechaFinalizacion: string | null };

/** Sufijo aleatorio de 5 caracteres, así "TEST_" + sufijo entra en los 10 caracteres del id de equipo. */
function sufijo(): string {
  return Math.random().toString(36).slice(2, 7).toUpperCase().padEnd(5, '0');
}

/** Suma días a una fecha AAAA-MM-DD. */
function sumarDias(fecha: string, dias: number): string {
  const d = new Date(`${fecha}T12:00:00Z`);
  d.setUTCDate(d.getUTCDate() + dias);
  return d.toISOString().slice(0, 10);
}

/** Fecha de hoy de la computadora con formato AAAA-MM-DD (igual que gestionPrestamos.js). */
function hoyLocal(): string {
  const hoy = new Date();
  return `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
}

/**
 * Crea datos TEST_ por la API con la sesión del técnico y los borra al final,
 * en orden inverso al de creación (reparaciones y soluciones antes que diagnósticos, etc.).
 */
class DatosPrueba {
  private registros: { recurso: string; id: string }[] = [];

  constructor(readonly api: APIRequestContext, private token: string) {}

  static async desdePagina(page: Page): Promise<DatosPrueba> {
    return new DatosPrueba(page.request, await tokenApi(page.request, 'VistaDeTickets.php'));
  }

  get cabeceras() {
    return { 'X-CSRF-Token': this.token };
  }

  anotar(recurso: string, id: string | number) {
    this.registros.push({ recurso, id: String(id) });
  }

  async crear(recurso: string, datos: object, campoId: string) {
    const respuesta = await this.api.post(`${URL_API}/${recurso}.php`, { headers: this.cabeceras, data: datos });
    expect(respuesta.status(), `alta en ${recurso}: ${await respuesta.text()}`).toBe(201);
    const cuerpo = await respuesta.json();
    this.anotar(recurso, cuerpo.datos[campoId]);
    return cuerpo.datos;
  }

  async modificar(recurso: string, id: string | number, datos: object) {
    const respuesta = await this.api.put(`${URL_API}/${recurso}.php?id=${encodeURIComponent(String(id))}`, { headers: this.cabeceras, data: datos });
    expect(respuesta.status(), `modificación en ${recurso}: ${await respuesta.text()}`).toBe(200);
    return (await respuesta.json()).datos;
  }

  async obtener(recurso: string, consulta: string) {
    const respuesta = await this.api.get(`${URL_API}/${recurso}.php?${consulta}`);
    expect(respuesta.status(), `consulta en ${recurso}: ${await respuesta.text()}`).toBe(200);
    return (await respuesta.json()).datos;
  }

  async crearEquipo(cambios: Partial<Equipo> = {}): Promise<Equipo> {
    return this.crear('equipos', {
      idEquipo: `TEST_${sufijo()}`,
      idLaboratorio: LAB_1.id,
      marca: 'HP',
      estado: 'Funcionando',
      disponibilidad: 'Disponible',
      informacion: 'TEST_ equipo de tecnico.spec.ts',
      ...cambios,
    }, 'idEquipo');
  }

  async crearTicket(equipo: Equipo, asunto: string): Promise<Ticket> {
    return this.crear('tickets', {
      laboratorio: equipo.idLaboratorio,
      equipo: equipo.idEquipo,
      asunto,
      descripcion: 'TEST_ ticket creado por tecnico.spec.ts',
      turno: 'Matutino',
      grupo: 'TEST_3BD',
      profesor: 'TEST_Profesor',
    }, 'idTicket');
  }

  async crearDiagnostico(idTicket: string, diagnostico: string) {
    return this.crear('diagnosticos', { idTicket, cedulaTecnico: CI_TECNICO, diagnostico }, 'idDiagnostico');
  }

  async limpiar() {
    const errores: string[] = [];
    for (const { recurso, id } of this.registros.reverse()) {
      const url = `${URL_API}/${recurso}.php?id=${encodeURIComponent(id)}`;
      if (recurso === 'prestamos') {
        // Un préstamo activo no se puede borrar, primero se devuelve (si ya estaba devuelto da 409).
        await this.api.put(url, { headers: this.cabeceras, data: { estado: 'Devuelto' } });
      }
      const respuesta = await this.api.delete(url, { headers: this.cabeceras });
      if (respuesta.status() !== 200 && respuesta.status() !== 404) {
        errores.push(`${recurso} ${id}: ${respuesta.status()} ${await respuesta.text()}`);
      }
    }
    this.registros = [];
    expect(errores, 'datos TEST_ que no se pudieron borrar').toEqual([]);
  }
}

/** Prepara un equipo TEST_ en LAB001 y un ticket TEST_ sobre ese equipo. */
async function prepararTicket(datos: DatosPrueba, nombre: string) {
  const equipo = await datos.crearEquipo();
  const ticket = await datos.crearTicket(equipo, `TEST_${nombre} ${Date.now()}`);
  return { equipo, ticket };
}

function articuloTicket(page: Page, idTicket: string): Locator {
  return page.locator(`article.ticket[data-id="${idTicket}"]`);
}

async function abrirVistaTickets(page: Page, ids: string[]) {
  await page.goto(`${URL_PAGINAS}/VistaDeTickets.php`);
  for (const id of ids) {
    await expect(articuloTicket(page, id)).toBeAttached();
  }
}

async function esperarRespuesta(page: Page, api: string, metodo: string) {
  return page.waitForResponse(r => r.url().includes(`/api/${api}`) && r.request().method() === metodo);
}

/** Registra si la página hace alguna petición del método indicado a la API. */
function vigilarPeticion(page: Page, api: string, metodo: string) {
  const estado = { enviada: false };
  page.on('request', r => {
    if (r.url().includes(`/api/${api}`) && r.method() === metodo) {
      estado.enviada = true;
    }
  });
  return estado;
}

async function campoVacio(page: Page, selector: string): Promise<boolean> {
  return page.locator(selector).evaluate((elemento: HTMLInputElement) => elemento.validity.valueMissing);
}

function filaConId(cuerpoTabla: Locator, id: string | number): Locator {
  return cuerpoTabla.locator('tr').filter({ has: cuerpoTabla.page().locator('td:first-child', { hasText: new RegExp(`^${id}$`) }) });
}

async function abrirRegistrarReparacion(page: Page, idDiagnostico: number) {
  await page.goto(`${URL_PAGINAS}/RegistrarReparacion.php`);
  await expect(page.locator(`#registrarReparacionDiagnostico option[value="${idDiagnostico}"]`)).toBeAttached();
}

async function registrarReparacionPorInterfaz(page: Page, idDiagnostico: number, texto: string) {
  await abrirRegistrarReparacion(page, idDiagnostico);
  await page.locator('#registrarReparacionDiagnostico').selectOption(String(idDiagnostico));
  await page.locator('#registrarReparacionTexto').fill(texto);
  const respuestaPromesa = esperarRespuesta(page, 'reparaciones.php', 'POST');
  await page.getByRole('button', { name: 'Registrar reparación' }).click();
  const respuesta = await respuestaPromesa;
  expect(respuesta.status()).toBe(201);
  const cuerpo = await respuesta.json();
  datos.anotar('reparaciones', cuerpo.datos.idReparacion);
  return cuerpo.datos;
}

let datos: DatosPrueba;

test.beforeEach(async ({ page }) => {
  await login(page, USUARIOS.tecnico);
  datos = await DatosPrueba.desdePagina(page);
});

test.afterEach(async () => {
  await datos?.limpiar();
});

test.describe('Panel técnico', () => {
  const ENLACES = [
    { texto: 'Solicitudes de laboratorio', pagina: 'VistaLab.php', titulo: 'Vista de Laboratorio' },
    { texto: 'Tickets', pagina: 'VistaDeTickets.php', titulo: 'Vista de Tickets' },
    { texto: 'Registrar diagnostico', pagina: 'RegistrarDiagnostico.php', titulo: 'Registro de Diagnostico' },
    { texto: 'Registrar reparacion', pagina: 'RegistrarReparacion.php', titulo: 'Registrar Reparación' },
    { texto: 'Registrar solucion', pagina: 'RegistrarSolucion.php', titulo: 'Registro de Soluciones' },
    { texto: 'Consultar diagnosticos', pagina: 'ConsultarDiagnostico.php', titulo: 'Consultar Diagnósticos' },
    { texto: 'Consultar equipos', pagina: 'Equipos.php', titulo: 'Datos de Equipos' },
    { texto: 'Préstamos de equipos', pagina: 'Prestamos.php', titulo: 'Préstamos y devoluciones de equipos' },
    { texto: 'Historial Tecnico', pagina: 'HistorialTecnico.php', titulo: 'Historial Técnico' },
  ];

  test('TEC-01 el panel muestra la bienvenida, las estadísticas y las opciones del menú', async ({ page }) => {
    await expect(page).toHaveURL(/Tecnico\.php/);
    await expect(page.locator('.encabezado h1')).toHaveText(/Bienvenido,\s+TEST_Tecnico\s+TEST_Playwright\s+\(Tecnico\)/);

    for (const id of ['#cantReportes', '#canIncidenciasAbiertas', '#cantEnProceso', '#cantResueltas']) {
      await expect(page.locator(id)).toHaveText(/^\d+$/);
    }
    for (const titulo of ['Incidencias por estado', 'Tiempos de Resolución', 'Incidencias por salón']) {
      await expect(page.getByRole('heading', { name: titulo })).toBeVisible();
    }
    for (const enlace of ENLACES) {
      await expect(page.getByRole('link', { name: enlace.texto, exact: true })).toBeVisible();
    }
    await expect(page.getByRole('link', { name: 'Cerrar sesion' })).toBeVisible();
  });

  // DEFECTO: Préstamos de equipos abre pero muestra "HTTP 500: Ocurrió un error, intente nuevamente." porque
  // la tabla PRESTAMO no existe en la base (DAOPrestamo.php:58, baseDeDatos/DDL/creacionPrestamo.sql no aplicado).
  test('TEC-02 cada enlace del menú abre su página sin errores', async ({ page }) => {
    const erroresJs: string[] = [];
    page.on('pageerror', error => erroresJs.push(`${page.url()}: ${error.message}`));

    for (const enlace of ENLACES) {
      await page.goto(`${URL_PAGINAS}/Tecnico.php`);
      await page.getByRole('link', { name: enlace.texto, exact: true }).click();
      await expect(page).toHaveURL(new RegExp(enlace.pagina));
      await expect(page.getByRole('heading', { name: enlace.titulo, exact: true })).toBeVisible();
      // Se espera a que terminen los fetch de la página para ver también los errores de la API.
      await page.waitForLoadState('networkidle');
      await expect.soft(page.locator('body'), enlace.pagina).not.toContainText(/Fatal error|Warning:|Notice:|Deprecated:|Ocurrió un error/);

      await page.getByRole('link', { name: 'Regresar', exact: true }).click();
      await expect(page).toHaveURL(/Tecnico\.php/);
    }

    expect(erroresJs).toEqual([]);
  });

  test('TEC-03 los contadores por estado suman el total de reportes', async ({ page }) => {
    const numero = async (id: string) => Number(await page.locator(id).textContent());

    const suma = await numero('#contador-Abiertas') + await numero('#contador-enProceso')
      + await numero('#contador-resueltas') + await numero('#contador-Cerradas');

    expect(suma).toBe(await numero('#cuentaTotal'));
    expect(await numero('#cantReportes')).toBe(await numero('#cuentaTotal'));
  });
});

test.describe('Tickets', () => {
  /** Crea tres tickets TEST_: Pendiente/Alta, En Proceso/Media y Resuelto/Baja. */
  async function crearTresTickets(marca: string) {
    const equipo = await datos.crearEquipo();
    const pendiente = await datos.crearTicket(equipo, `TEST_${marca} pendiente`);
    await datos.modificar('tickets', pendiente.idTicket, { estado: 'Pendiente', prioridad: 'Alta' });
    const enProceso = await datos.crearTicket(equipo, `TEST_${marca} en proceso`);
    await datos.modificar('tickets', enProceso.idTicket, { estado: 'En Proceso', prioridad: 'Media' });
    const resuelto = await datos.crearTicket(equipo, `TEST_${marca} resuelto`);
    await datos.modificar('tickets', resuelto.idTicket, { estado: 'Resuelto', prioridad: 'Baja' });
    return { equipo, pendiente, enProceso, resuelto };
  }

  async function verificarVisibles(page: Page, visibles: Ticket[], ocultos: Ticket[]) {
    for (const ticket of visibles) {
      await expect(articuloTicket(page, ticket.idTicket), `${ticket.asunto} debería verse`).toBeVisible();
    }
    for (const ticket of ocultos) {
      await expect(articuloTicket(page, ticket.idTicket), `${ticket.asunto} debería ocultarse`).toBeHidden();
    }
  }

  test('TEC-04 ver el listado de tickets con estado, prioridad y fecha de finalización', async ({ page }) => {
    const { equipo, pendiente, enProceso, resuelto } = await crearTresTickets(sufijo());
    await abrirVistaTickets(page, [pendiente.idTicket, enProceso.idTicket, resuelto.idTicket]);

    const articulo = articuloTicket(page, pendiente.idTicket);
    await expect(articulo.getByRole('link', { name: pendiente.idTicket })).toHaveAttribute('href', `ConsultarDiagnostico.php?ticket=${pendiente.idTicket}`);
    await expect(articulo).toContainText(pendiente.asunto);
    await expect(articulo).toContainText(equipo.idEquipo);
    await expect(articulo.locator('.laboratorio')).toHaveText(LAB_1.id);
    await expect(articulo.locator('.select-estado')).toHaveValue('Pendiente');
    await expect(articulo.locator('.select-prioridad')).toHaveValue('Alta');
    await expect(articulo).not.toContainText('Finalizado:');

    await expect(articuloTicket(page, enProceso.idTicket).locator('.select-estado')).toHaveValue('En Proceso');
    await expect(articuloTicket(page, enProceso.idTicket).locator('.select-prioridad')).toHaveValue('Media');
    await expect(articuloTicket(page, resuelto.idTicket).locator('.select-estado')).toHaveValue('Resuelto');
    await expect(articuloTicket(page, resuelto.idTicket)).toContainText(/Finalizado: \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/);
  });

  test('TEC-05 filtrar tickets por prioridad', async ({ page }) => {
    const { pendiente, enProceso, resuelto } = await crearTresTickets(sufijo());
    await abrirVistaTickets(page, [pendiente.idTicket, enProceso.idTicket, resuelto.idTicket]);

    await page.locator('#filtroPrioridad').selectOption('Alta');
    await verificarVisibles(page, [pendiente], [enProceso, resuelto]);

    await page.locator('#filtroPrioridad').selectOption('Baja');
    await verificarVisibles(page, [resuelto], [pendiente, enProceso]);

    await page.locator('#filtroPrioridad').selectOption('Indefinida');
    await verificarVisibles(page, [], [pendiente, enProceso, resuelto]);

    await page.locator('#filtroPrioridad').selectOption('');
    await verificarVisibles(page, [pendiente, enProceso, resuelto], []);
  });

  test('TEC-06 filtrar tickets por estado y combinar con prioridad', async ({ page }) => {
    const { pendiente, enProceso, resuelto } = await crearTresTickets(sufijo());
    await abrirVistaTickets(page, [pendiente.idTicket, enProceso.idTicket, resuelto.idTicket]);

    await page.locator('#filtroEstado').selectOption('En Proceso');
    await verificarVisibles(page, [enProceso], [pendiente, resuelto]);

    await page.locator('#filtroEstado').selectOption('Resuelto');
    await verificarVisibles(page, [resuelto], [pendiente, enProceso]);

    await page.locator('#filtroEstado').selectOption('Cerrado');
    await verificarVisibles(page, [], [pendiente, enProceso, resuelto]);

    // Estado Resuelto + prioridad Alta no coincide con ninguno de los tres.
    await page.locator('#filtroEstado').selectOption('Resuelto');
    await page.locator('#filtroPrioridad').selectOption('Alta');
    await verificarVisibles(page, [], [pendiente, enProceso, resuelto]);
  });

  test('TEC-07 filtrar tickets por rango de fechas y buscar por número', async ({ page }) => {
    const { pendiente, enProceso, resuelto } = await crearTresTickets(sufijo());
    await abrirVistaTickets(page, [pendiente.idTicket, enProceso.idTicket, resuelto.idTicket]);
    const dia = pendiente.fechaCreacion.slice(0, 10);
    const todos = [pendiente, enProceso, resuelto];

    await page.locator('#fechaDesde').fill(dia);
    await page.locator('#fechaHasta').fill(dia);
    await page.getByRole('button', { name: 'Filtrar fechas' }).click();
    await verificarVisibles(page, todos, []);

    await page.locator('#fechaDesde').fill(sumarDias(dia, 1));
    await page.locator('#fechaHasta').fill('');
    await page.getByRole('button', { name: 'Filtrar fechas' }).click();
    await verificarVisibles(page, [], todos);

    await page.locator('#fechaDesde').fill('');
    await page.locator('#fechaHasta').fill(sumarDias(dia, -1));
    await page.getByRole('button', { name: 'Filtrar fechas' }).click();
    await verificarVisibles(page, [], todos);

    await page.locator('#fechaDesde').fill(sumarDias(dia, -3));
    await page.locator('#fechaHasta').fill(sumarDias(dia, 3));
    await page.getByRole('button', { name: 'Filtrar fechas' }).click();
    await verificarVisibles(page, todos, []);

    await page.locator('#buscadorDeTickets').fill(enProceso.idTicket);
    await page.getByRole('button', { name: 'Buscar', exact: true }).click();
    await verificarVisibles(page, [enProceso], [pendiente, resuelto]);
  });

  test('TEC-08 la API filtra tickets por estado, prioridad y fechas', async () => {
    const marca = sufijo();
    const { pendiente, enProceso, resuelto } = await crearTresTickets(marca);
    const dia = pendiente.fechaCreacion.slice(0, 10);
    const ids = async (consulta: string) =>
      ((await datos.obtener('tickets', `q=TEST_${marca}&${consulta}`)) as Ticket[]).map(t => t.idTicket).sort();

    expect(await ids('')).toEqual([pendiente.idTicket, enProceso.idTicket, resuelto.idTicket].sort());
    expect(await ids('estado=En%20Proceso')).toEqual([enProceso.idTicket]);
    expect(await ids('prioridad=Alta')).toEqual([pendiente.idTicket]);
    expect(await ids('estado=Resuelto&prioridad=Baja')).toEqual([resuelto.idTicket]);
    expect(await ids(`fechaDesde=${dia}&fechaHasta=${dia}`)).toHaveLength(3);
    expect(await ids(`fechaDesde=${sumarDias(dia, 1)}`)).toEqual([]);
    expect(await ids(`fechaHasta=${sumarDias(dia, -1)}`)).toEqual([]);
  });

  test('TEC-09 la API rechaza un filtro de estado o prioridad no permitido', async () => {
    const estado = await datos.api.get(`${URL_API}/tickets.php?estado=Inexistente`);
    expect(estado.status()).toBe(400);
    expect((await estado.json()).mensaje).toBe('El filtro estado tiene un valor no permitido.');

    const prioridad = await datos.api.get(`${URL_API}/tickets.php?prioridad=Urgente`);
    expect(prioridad.status()).toBe(400);
    expect((await prioridad.json()).mensaje).toBe('El filtro prioridad tiene un valor no permitido.');
  });

  // DEFECTO: ControladorTicket::obtenerFiltros no valida fechaDesde ni fechaHasta (ControladorTicket.php:166-167),
  // a diferencia de estado y prioridad: "fechaDesde=hola" devuelve 200 con todos los tickets y "fechaHasta=xx" 200 vacío.
  test('TEC-10 la API rechaza un filtro de fecha con formato inválido', async () => {
    const desde = await datos.api.get(`${URL_API}/tickets.php?fechaDesde=hola`);
    expect(desde.status()).toBe(400);

    const hasta = await datos.api.get(`${URL_API}/tickets.php?fechaHasta=2026-13-45`);
    expect(hasta.status()).toBe(400);
  });

  test('TEC-11 cambiar estado y prioridad de un ticket desde la interfaz', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'cambio');
    await abrirVistaTickets(page, [ticket.idTicket]);
    const articulo = articuloTicket(page, ticket.idTicket);

    let respuesta = esperarRespuesta(page, 'tickets.php', 'PUT');
    await articulo.locator('.select-estado').selectOption('En Proceso');
    expect((await respuesta).status()).toBe(200);

    respuesta = esperarRespuesta(page, 'tickets.php', 'PUT');
    await articulo.locator('.select-prioridad').selectOption('Alta');
    expect((await respuesta).status()).toBe(200);

    const guardado: Ticket = await datos.obtener('tickets', `id=${ticket.idTicket}`);
    expect(guardado.estado).toBe('En Proceso');
    expect(guardado.prioridad).toBe('Alta');
    expect(guardado.fechaFinalizacion).toBeNull();

    await page.reload();
    await expect(articuloTicket(page, ticket.idTicket).locator('.select-estado')).toHaveValue('En Proceso');
    await expect(articuloTicket(page, ticket.idTicket).locator('.select-prioridad')).toHaveValue('Alta');
  });

  test('TEC-12 al pasar a Resuelto o Cerrado se registra la fecha de finalización', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'finalizar');
    await abrirVistaTickets(page, [ticket.idTicket]);
    const selectEstado = articuloTicket(page, ticket.idTicket).locator('.select-estado');

    let respuesta = esperarRespuesta(page, 'tickets.php', 'PUT');
    await selectEstado.selectOption('Resuelto');
    expect((await respuesta).status()).toBe(200);

    const resuelto: Ticket = await datos.obtener('tickets', `id=${ticket.idTicket}`);
    expect(resuelto.estado).toBe('Resuelto');
    expect(resuelto.fechaFinalizacion).toMatch(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/);

    await page.reload();
    await expect(articuloTicket(page, ticket.idTicket)).toContainText(`Finalizado: ${resuelto.fechaFinalizacion}`);

    // Al cerrarlo se conserva la fecha de finalización original.
    respuesta = esperarRespuesta(page, 'tickets.php', 'PUT');
    await articuloTicket(page, ticket.idTicket).locator('.select-estado').selectOption('Cerrado');
    expect((await respuesta).status()).toBe(200);
    const cerrado: Ticket = await datos.obtener('tickets', `id=${ticket.idTicket}`);
    expect(cerrado.estado).toBe('Cerrado');
    expect(cerrado.fechaFinalizacion).toBe(resuelto.fechaFinalizacion);

    // Si se reabre, la fecha de finalización se borra.
    respuesta = esperarRespuesta(page, 'tickets.php', 'PUT');
    await articuloTicket(page, ticket.idTicket).locator('.select-estado').selectOption('En Proceso');
    expect((await respuesta).status()).toBe(200);
    expect((await datos.obtener('tickets', `id=${ticket.idTicket}`)).fechaFinalizacion).toBeNull();
  });
});

test.describe('Diagnóstico', () => {
  const MENSAJE_LONGITUD = 'El campo diagnóstico debe tener entre 10 y 2000 caracteres.';

  async function abrirRegistrarDiagnostico(page: Page, idTicket: string) {
    await page.goto(`${URL_PAGINAS}/RegistrarDiagnostico.php`);
    await expect(page.locator(`#registrarDiagnosticoTicket option[value="${idTicket}"]`)).toBeAttached();
  }

  test('TEC-13 registrar un diagnóstico correcto', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'diag');
    await abrirRegistrarDiagnostico(page, ticket.idTicket);
    const texto = 'TEST_ La fuente de poder no entrega tensión.';

    await page.locator('#registrarDiagnosticoTicket').selectOption(ticket.idTicket);
    await page.locator('#registrarDiagnosticoDiagnostico').fill(texto);
    const respuestaPromesa = esperarRespuesta(page, 'diagnosticos.php', 'POST');
    await page.getByRole('button', { name: 'Registrar diagnóstico' }).click();
    const respuesta = await respuestaPromesa;

    expect(respuesta.status()).toBe(201);
    const cuerpo = await respuesta.json();
    datos.anotar('diagnosticos', cuerpo.datos.idDiagnostico);
    expect(cuerpo.datos).toMatchObject({ idTicket: ticket.idTicket, cedulaTecnico: CI_TECNICO, diagnostico: texto });

    const mensaje = page.locator('#mensajeRegistrarDiagnostico');
    await expect(mensaje).toHaveText(`Diagnóstico ${cuerpo.datos.idDiagnostico} registrado correctamente.`);
    await expect(mensaje).toHaveClass('mensaje-exito');
    await expect(page.locator('#registrarDiagnosticoTicket')).toHaveValue('');
    await expect(page.locator('#registrarDiagnosticoDiagnostico')).toHaveValue('');

    const guardados = await datos.obtener('diagnosticos', `idTicket=${ticket.idTicket}`);
    expect(guardados).toHaveLength(1);
  });

  test('TEC-14 registrar un diagnóstico con campos vacíos no se envía', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'diagvacio');
    await abrirRegistrarDiagnostico(page, ticket.idTicket);
    const peticion = vigilarPeticion(page, 'diagnosticos.php', 'POST');

    await page.getByRole('button', { name: 'Registrar diagnóstico' }).click();

    expect(await campoVacio(page, '#registrarDiagnosticoTicket')).toBe(true);
    expect(await campoVacio(page, '#registrarDiagnosticoDiagnostico')).toBe(true);
    await expect(page.locator('#mensajeRegistrarDiagnostico')).toHaveText('');
    expect(peticion.enviada).toBe(false);
  });

  test('TEC-15 registrar un diagnóstico solo con espacios muestra el error del servidor', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'diagesp');
    await abrirRegistrarDiagnostico(page, ticket.idTicket);

    await page.locator('#registrarDiagnosticoTicket').selectOption(ticket.idTicket);
    await page.locator('#registrarDiagnosticoDiagnostico').fill('            ');
    const respuesta = esperarRespuesta(page, 'diagnosticos.php', 'POST');
    await page.getByRole('button', { name: 'Registrar diagnóstico' }).click();

    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajeRegistrarDiagnostico')).toHaveText(MENSAJE_LONGITUD);
    await expect(page.locator('#mensajeRegistrarDiagnostico')).toHaveClass('mensaje-error');
    expect(await datos.obtener('diagnosticos', `idTicket=${ticket.idTicket}`)).toEqual([]);
  });

  test('TEC-16 consultar los diagnósticos filtrando por ticket', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'consulta');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Disco duro con sectores dañados.');

    await page.goto(`${URL_PAGINAS}/ConsultarDiagnostico.php`);
    await expect(page.locator('#tituloConsultarDiagnosticos')).toHaveText('Diagnósticos registrados');
    await page.locator('#filtroTicket').fill(ticket.idTicket);
    await page.getByRole('button', { name: 'Filtrar' }).click();

    await expect(page).toHaveURL(new RegExp(`ticket=${ticket.idTicket}`));
    await expect(page.locator('#tituloConsultarDiagnosticos')).toHaveText(`Diagnósticos del ticket ${ticket.idTicket}`);
    const filas = page.locator('#cuerpoTablaDiagnosticos tr');
    await expect(filas).toHaveCount(1);
    await expect(filas.first().locator('td')).toHaveText([
      String(diagnostico.idDiagnostico),
      ticket.idTicket,
      diagnostico.diagnostico,
      /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/,
      CI_TECNICO,
    ]);

    await page.getByRole('button', { name: 'Quitar filtro' }).click();
    await expect(page.locator('#tituloConsultarDiagnosticos')).toHaveText('Diagnósticos registrados');
    await expect(filaConId(page.locator('#cuerpoTablaDiagnosticos'), diagnostico.idDiagnostico)).toHaveCount(1);
  });

  test('TEC-17 el enlace del ticket abre sus diagnósticos y avisa si no tiene', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'sindiag');
    await abrirVistaTickets(page, [ticket.idTicket]);

    await articuloTicket(page, ticket.idTicket).getByRole('link', { name: ticket.idTicket }).click();

    await expect(page).toHaveURL(new RegExp(`ConsultarDiagnostico\\.php\\?ticket=${ticket.idTicket}`));
    await expect(page.locator('#cuerpoTablaDiagnosticos')).toHaveText(`No hay diagnósticos registrados para el ticket ${ticket.idTicket}.`);
  });

  test('TEC-18 modificar un diagnóstico', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'modif');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico original del equipo.');
    const nuevoTexto = 'TEST_ Diagnóstico corregido: memoria RAM defectuosa.';

    await page.goto(`${URL_PAGINAS}/ModificarDiagnostico.php`);
    const select = page.locator('#modificarDiagnosticoSelect');
    await expect(select.locator(`option[value="${diagnostico.idDiagnostico}"]`)).toBeAttached();
    await select.selectOption(String(diagnostico.idDiagnostico));
    await expect(page.locator('#modificarDiagnosticoTexto')).toHaveValue(diagnostico.diagnostico);

    await page.locator('#modificarDiagnosticoTexto').fill(nuevoTexto);
    const respuesta = esperarRespuesta(page, 'diagnosticos.php', 'PUT');
    await page.getByRole('button', { name: 'Guardar cambios' }).click();

    expect((await respuesta).status()).toBe(200);
    await expect(page.locator('#mensajeModificarDiagnostico')).toHaveText(`Diagnóstico ${diagnostico.idDiagnostico} actualizado correctamente.`);
    await expect(page.locator('#mensajeModificarDiagnostico')).toHaveClass('mensaje-exito');
    expect((await datos.obtener('diagnosticos', `id=${diagnostico.idDiagnostico}`)).diagnostico).toBe(nuevoTexto);
  });

  test('TEC-19 modificar un diagnóstico con el texto vacío o con espacios no lo cambia', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'modifvacio');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico que no debe cambiar.');

    await page.goto(`${URL_PAGINAS}/ModificarDiagnostico.php`);
    await expect(page.locator(`#modificarDiagnosticoSelect option[value="${diagnostico.idDiagnostico}"]`)).toBeAttached();
    await page.locator('#modificarDiagnosticoSelect').selectOption(String(diagnostico.idDiagnostico));
    const peticion = vigilarPeticion(page, 'diagnosticos.php', 'PUT');

    await page.locator('#modificarDiagnosticoTexto').fill('');
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    expect(await campoVacio(page, '#modificarDiagnosticoTexto')).toBe(true);
    expect(peticion.enviada).toBe(false);

    await page.locator('#modificarDiagnosticoTexto').fill('            ');
    const respuesta = esperarRespuesta(page, 'diagnosticos.php', 'PUT');
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajeModificarDiagnostico')).toHaveText(MENSAJE_LONGITUD);
    await expect(page.locator('#mensajeModificarDiagnostico')).toHaveClass('mensaje-error');

    expect((await datos.obtener('diagnosticos', `id=${diagnostico.idDiagnostico}`)).diagnostico).toBe(diagnostico.diagnostico);
  });
});

test.describe('Solución', () => {
  async function abrirRegistrarSolucion(page: Page, idDiagnostico: number) {
    await page.goto(`${URL_PAGINAS}/RegistrarSolucion.php`);
    await expect(page.locator(`#registrarSolucionDiagnostico option[value="${idDiagnostico}"]`)).toBeAttached();
  }

  test('TEC-20 registrar una solución correcta', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'sol');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Cable de video dañado.');
    await abrirRegistrarSolucion(page, diagnostico.idDiagnostico);
    const texto = 'TEST_ Se reemplazó el cable HDMI por uno nuevo.';

    await page.locator('#registrarSolucionDiagnostico').selectOption(String(diagnostico.idDiagnostico));
    await page.locator('#registrarSolucionSolucion').fill(texto);
    const respuestaPromesa = esperarRespuesta(page, 'soluciones.php', 'POST');
    await page.getByRole('button', { name: 'Registrar solución' }).click();
    const respuesta = await respuestaPromesa;

    expect(respuesta.status()).toBe(201);
    const cuerpo = await respuesta.json();
    datos.anotar('soluciones', cuerpo.datos.idSolucion);

    await expect(page.locator('#mensajeRegistrarSolucion')).toHaveText(`Solución ${cuerpo.datos.idSolucion} registrada correctamente.`);
    await expect(page.locator('#mensajeRegistrarSolucion')).toHaveClass('mensaje-exito');
    await expect(page.locator('#registrarSolucionSolucion')).toHaveValue('');

    const guardada = await datos.obtener('soluciones', `id=${cuerpo.datos.idSolucion}`);
    expect(guardada).toMatchObject({
      idDiagnostico: diagnostico.idDiagnostico,
      idTicket: ticket.idTicket,
      cedulaTecnico: CI_TECNICO,
      solucion: texto,
    });
  });

  test('TEC-21 registrar una solución con campos vacíos no se envía', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'solvacio');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico para solución vacía.');
    await abrirRegistrarSolucion(page, diagnostico.idDiagnostico);
    const peticion = vigilarPeticion(page, 'soluciones.php', 'POST');

    await page.getByRole('button', { name: 'Registrar solución' }).click();

    expect(await campoVacio(page, '#registrarSolucionDiagnostico')).toBe(true);
    expect(await campoVacio(page, '#registrarSolucionSolucion')).toBe(true);
    await expect(page.locator('#mensajeRegistrarSolucion')).toHaveText('');
    expect(peticion.enviada).toBe(false);
  });

  test('TEC-22 registrar una solución solo con espacios muestra el error del servidor', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'solesp');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico para solución con espacios.');
    await abrirRegistrarSolucion(page, diagnostico.idDiagnostico);

    await page.locator('#registrarSolucionDiagnostico').selectOption(String(diagnostico.idDiagnostico));
    await page.locator('#registrarSolucionSolucion').fill('            ');
    const respuesta = esperarRespuesta(page, 'soluciones.php', 'POST');
    await page.getByRole('button', { name: 'Registrar solución' }).click();

    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajeRegistrarSolucion')).toHaveText('El campo solución debe tener entre 10 y 2000 caracteres.');
    await expect(page.locator('#mensajeRegistrarSolucion')).toHaveClass('mensaje-error');
  });
});

test.describe('Reparación', () => {
  test('TEC-23 registrar una reparación correcta', async ({ page }) => {
    const { equipo, ticket } = await prepararTicket(datos, 'rep');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Ventilador del procesador trabado.');
    const texto = 'TEST_ Se limpió y lubricó el ventilador del procesador.';

    const reparacion = await registrarReparacionPorInterfaz(page, diagnostico.idDiagnostico, texto);

    await expect(page.locator('#mensajeRegistrarReparacion')).toHaveText(`Reparación ${reparacion.idReparacion} registrada correctamente.`);
    await expect(page.locator('#mensajeRegistrarReparacion')).toHaveClass('mensaje-exito');
    await expect(page.locator('#registrarReparacionTexto')).toHaveValue('');

    // El ticket y el equipo se completan a partir del diagnóstico.
    const guardada = await datos.obtener('reparaciones', `id=${reparacion.idReparacion}`);
    expect(guardada).toMatchObject({
      idDiagnostico: diagnostico.idDiagnostico,
      idTicket: ticket.idTicket,
      idEquipo: equipo.idEquipo,
      cedulaTecnico: CI_TECNICO,
      reparacion: texto,
    });
  });

  test('TEC-24 registrar una reparación con campos vacíos no se envía', async ({ page }) => {
    const { ticket } = await prepararTicket(datos, 'repvacio');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico para reparación vacía.');
    await abrirRegistrarReparacion(page, diagnostico.idDiagnostico);
    const peticion = vigilarPeticion(page, 'reparaciones.php', 'POST');

    await page.getByRole('button', { name: 'Registrar reparación' }).click();

    expect(await campoVacio(page, '#registrarReparacionDiagnostico')).toBe(true);
    expect(await campoVacio(page, '#registrarReparacionTexto')).toBe(true);
    await expect(page.locator('#mensajeRegistrarReparacion')).toHaveText('');
    expect(peticion.enviada).toBe(false);
  });

  test('TEC-25 registrar una reparación solo con espacios muestra el error del servidor', async ({ page }) => {
    const { equipo, ticket } = await prepararTicket(datos, 'repesp');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Diagnóstico para reparación con espacios.');
    await abrirRegistrarReparacion(page, diagnostico.idDiagnostico);

    await page.locator('#registrarReparacionDiagnostico').selectOption(String(diagnostico.idDiagnostico));
    await page.locator('#registrarReparacionTexto').fill('            ');
    const respuesta = esperarRespuesta(page, 'reparaciones.php', 'POST');
    await page.getByRole('button', { name: 'Registrar reparación' }).click();

    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajeRegistrarReparacion')).toHaveText('El campo reparación debe tener entre 10 y 2000 caracteres.');
    await expect(page.locator('#mensajeRegistrarReparacion')).toHaveClass('mensaje-error');
    expect(await datos.obtener('reparaciones', `idEquipo=${equipo.idEquipo}`)).toEqual([]);
  });
});

test.describe('Historial técnico', () => {
  test('TEC-26 el historial se muestra y avisa si el equipo no tiene reparaciones', async ({ page }) => {
    const equipo = await datos.crearEquipo();

    await page.goto(`${URL_PAGINAS}/HistorialTecnico.php`);
    await expect(page.getByRole('heading', { name: 'Historial Técnico', exact: true })).toBeVisible();
    const select = page.locator('#historialTecnicoEquipoSelect');
    await expect(select.locator(`option[value="${equipo.idEquipo}"]`)).toBeAttached();

    await select.selectOption(equipo.idEquipo);
    await expect(page).toHaveURL(new RegExp(`equipo=${equipo.idEquipo}`));
    await expect(page.locator('#tablaHistorialTecnico')).toHaveText('No hay reparaciones registradas para este equipo.');
  });

  test('TEC-27 el historial contiene la reparación registrada por el test', async ({ page }) => {
    const { equipo, ticket } = await prepararTicket(datos, 'hist');
    const diagnostico = await datos.crearDiagnostico(ticket.idTicket, 'TEST_ Teclado con teclas que no responden.');
    const texto = 'TEST_ Se reemplazó el teclado del equipo.';
    const reparacion = await registrarReparacionPorInterfaz(page, diagnostico.idDiagnostico, texto);

    await page.goto(`${URL_PAGINAS}/HistorialTecnico.php`);
    await expect(page.locator(`#historialTecnicoEquipoSelect option[value="${equipo.idEquipo}"]`)).toBeAttached();
    await page.locator('#historialTecnicoEquipoSelect').selectOption(equipo.idEquipo);

    const tabla = page.locator('#tablaHistorialTecnico');
    await expect(tabla.locator('th')).toHaveText(['Ticket', 'Descripción', 'Fecha', 'Técnico']);
    await expect(tabla.locator('tbody tr')).toHaveCount(1);
    await expect(tabla.locator('tbody tr td')).toHaveText([ticket.idTicket, texto, reparacion.fechaReparacion, CI_TECNICO]);

    // Abriendo la página con ?equipo= se carga el historial directamente.
    await page.goto(`${URL_PAGINAS}/HistorialTecnico.php?equipo=${equipo.idEquipo}`);
    await expect(page.locator('#tablaHistorialTecnico tbody tr td').nth(1)).toHaveText(texto);
  });
});

test.describe('Equipos', () => {
  async function abrirEquipos(page: Page, ids: string[]) {
    await page.goto(`${URL_PAGINAS}/Equipos.php`);
    for (const id of ids) {
      await expect(filaConId(page.locator('#cuerpoTablaPc'), id)).toHaveCount(1);
    }
  }

  function filaEquipo(page: Page, id: string) {
    return filaConId(page.locator('#cuerpoTablaPc'), id);
  }

  async function modificarEquipo(page: Page, id: string, cambios: { idLaboratorio?: string; estado?: string }) {
    await filaEquipo(page, id).getByRole('button', { name: 'Modificar' }).click();
    await expect(page.locator('#idEquipo')).toHaveValue(id);
    await expect(page.locator('#leyendaFormularioEquipo')).toHaveText('Modificar equipo');
    if (cambios.idLaboratorio) {
      await page.locator('#idLaboratorio').selectOption(cambios.idLaboratorio);
    }
    if (cambios.estado) {
      await page.locator('#estado').selectOption(cambios.estado);
    }
    const respuesta = esperarRespuesta(page, 'equipos.php', 'PUT');
    await page.getByRole('button', { name: 'Guardar equipo' }).click();
    expect((await respuesta).status()).toBe(200);
    await expect(page.locator('#mensajeEquipos')).toHaveText(`Equipo ${id} actualizado correctamente.`);
  }

  test('TEC-28 listar equipos muestra los datos del equipo', async ({ page }) => {
    const equipo = await datos.crearEquipo({ marca: 'Lenovo', informacion: 'TEST_ equipo para listar' });
    await abrirEquipos(page, [equipo.idEquipo]);

    await expect(filaEquipo(page, equipo.idEquipo).locator('td')).toHaveText([
      equipo.idEquipo, LAB_1.numero, 'Lenovo', 'Funcionando', 'Disponible', 'TEST_ equipo para listar', /Modificar\s*Eliminar/,
    ]);
  });

  test('TEC-29 filtrar equipos por id, estado y laboratorio', async ({ page }) => {
    const funcionando = await datos.crearEquipo();
    const danado = await datos.crearEquipo({ idLaboratorio: LAB_2.id, estado: 'Dañado', disponibilidad: 'No disponible' });
    await abrirEquipos(page, [funcionando.idEquipo, danado.idEquipo]);

    await page.locator('#filtroID').fill(funcionando.idEquipo);
    await expect(filaEquipo(page, funcionando.idEquipo)).toBeVisible();
    await expect(filaEquipo(page, danado.idEquipo)).toBeHidden();

    await page.locator('#filtroID').fill('test_');
    await page.locator('#filtroEstado').fill('dañado');
    await expect(filaEquipo(page, danado.idEquipo)).toBeVisible();
    await expect(filaEquipo(page, funcionando.idEquipo)).toBeHidden();

    await page.locator('#filtroEstado').fill('');
    await page.locator('#filtroLab').fill(LAB_1.numero);
    await expect(filaEquipo(page, funcionando.idEquipo)).toBeVisible();
    await expect(filaEquipo(page, danado.idEquipo)).toBeHidden();

    await page.locator('#filtroLab').fill('');
    await page.locator('#filtroDisponibilidad').fill('no disponible');
    await expect(filaEquipo(page, danado.idEquipo)).toBeVisible();
    await expect(filaEquipo(page, funcionando.idEquipo)).toBeHidden();
  });

  // DEFECTO: el filtro de disponibilidad compara con includes (filtrosEquipos.js:28-31), y como "No disponible"
  // contiene "disponible", buscar "Disponible" sigue mostrando los equipos no disponibles.
  test('TEC-30 filtrar equipos por disponibilidad "Disponible" oculta los no disponibles', async ({ page }) => {
    const disponible = await datos.crearEquipo();
    const noDisponible = await datos.crearEquipo({ disponibilidad: 'No disponible' });
    await abrirEquipos(page, [disponible.idEquipo, noDisponible.idEquipo]);

    await page.locator('#filtroDisponibilidad').fill('Disponible');

    await expect(filaEquipo(page, disponible.idEquipo)).toBeVisible();
    await expect(filaEquipo(page, noDisponible.idEquipo)).toBeHidden();
  });

  test('TEC-31 cambiar el estado de un equipo', async ({ page }) => {
    const equipo = await datos.crearEquipo();
    await abrirEquipos(page, [equipo.idEquipo]);

    await modificarEquipo(page, equipo.idEquipo, { estado: 'En mantenimiento' });

    await expect(filaEquipo(page, equipo.idEquipo).locator('td').nth(3)).toHaveText('En mantenimiento');
    await expect(page.locator('#leyendaFormularioEquipo')).toHaveText('Alta de equipo');
    const guardado: Equipo = await datos.obtener('equipos', `id=${equipo.idEquipo}`);
    expect(guardado.estado).toBe('En mantenimiento');
    expect(guardado.idLaboratorio).toBe(LAB_1.id);
  });

  test('TEC-32 cambiar la ubicación (laboratorio) de un equipo', async ({ page }) => {
    const equipo = await datos.crearEquipo();
    await abrirEquipos(page, [equipo.idEquipo]);

    await modificarEquipo(page, equipo.idEquipo, { idLaboratorio: LAB_2.id });

    await expect(filaEquipo(page, equipo.idEquipo).locator('td').nth(1)).toHaveText(LAB_2.numero);
    const guardado: Equipo = await datos.obtener('equipos', `id=${equipo.idEquipo}`);
    expect(guardado.idLaboratorio).toBe(LAB_2.id);
    expect(guardado.estado).toBe('Funcionando');
  });

  test('TEC-33 dar de alta y eliminar un equipo desde la interfaz', async ({ page }) => {
    const idEquipo = `TEST_${sufijo()}`;
    await abrirEquipos(page, []);

    await page.locator('#idEquipo').fill(idEquipo);
    await page.locator('#idLaboratorio').selectOption(LAB_1.id);
    await page.locator('#marca').selectOption('Acer');
    await page.locator('#estado').selectOption('Funcionando');
    await page.locator('#disponibilidad').selectOption('Disponible');
    await page.locator('#informacion').fill('TEST_ alta por interfaz');
    const alta = esperarRespuesta(page, 'equipos.php', 'POST');
    await page.getByRole('button', { name: 'Guardar equipo' }).click();
    expect((await alta).status()).toBe(201);
    datos.anotar('equipos', idEquipo);

    await expect(page.locator('#mensajeEquipos')).toHaveText(`Equipo ${idEquipo} registrado correctamente.`);
    await expect(filaEquipo(page, idEquipo).locator('td').nth(2)).toHaveText('Acer');

    const baja = esperarRespuesta(page, 'equipos.php', 'DELETE');
    await filaEquipo(page, idEquipo).getByRole('button', { name: 'Eliminar' }).click();
    expect((await baja).status()).toBe(200);
    await expect(page.locator('#mensajeEquipos')).toHaveText(`Equipo ${idEquipo} eliminado correctamente.`);
    await expect(filaEquipo(page, idEquipo)).toHaveCount(0);
  });

  test('TEC-34 alta de equipo con datos inválidos', async ({ page }) => {
    const existente = await datos.crearEquipo();
    await abrirEquipos(page, [existente.idEquipo]);
    const peticion = vigilarPeticion(page, 'equipos.php', 'POST');

    await page.getByRole('button', { name: 'Guardar equipo' }).click();
    expect(await campoVacio(page, '#idEquipo')).toBe(true);
    expect(await campoVacio(page, '#idLaboratorio')).toBe(true);
    expect(peticion.enviada).toBe(false);

    await page.locator('#idEquipo').fill('TEST_123456');
    await page.locator('#idLaboratorio').selectOption(LAB_1.id);
    let respuesta = esperarRespuesta(page, 'equipos.php', 'POST');
    await page.getByRole('button', { name: 'Guardar equipo' }).click();
    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajeEquipos')).toHaveText('El campo idEquipo debe tener entre 1 y 10 caracteres.');

    await page.locator('#idEquipo').fill(existente.idEquipo);
    respuesta = esperarRespuesta(page, 'equipos.php', 'POST');
    await page.getByRole('button', { name: 'Guardar equipo' }).click();
    expect((await respuesta).status()).toBe(409);
    await expect(page.locator('#mensajeEquipos')).toHaveText(`Ya existe un equipo con el identificador ${existente.idEquipo}.`);

    const filtro = await datos.api.get(`${URL_API}/equipos.php?estado=Roto`);
    expect(filtro.status()).toBe(400);
  });

  // DEFECTO: la tabla TICKET de la base no tiene las FK fk_ticket_equipo/fk_ticket_laboratorio que define
  // baseDeDatos/DDL/creacionTicket.sql:18-24, así que el DELETE no da el error 23000 que ControladorEquipo.php:79 convierte
  // en 409: el equipo se borra (200) y sus tickets quedan apuntando a un equipo inexistente.
  test('TEC-35 no se puede eliminar un equipo que tiene tickets', async ({ page }) => {
    const { equipo } = await prepararTicket(datos, 'eqconticket');
    await abrirEquipos(page, [equipo.idEquipo]);

    const respuesta = esperarRespuesta(page, 'equipos.php', 'DELETE');
    await filaEquipo(page, equipo.idEquipo).getByRole('button', { name: 'Eliminar' }).click();

    expect((await respuesta).status()).toBe(409);
    await expect(page.locator('#mensajeEquipos')).toHaveText('No se puede eliminar el equipo porque tiene registros relacionados.');
    await expect(filaEquipo(page, equipo.idEquipo)).toHaveCount(1);
  });
});

test.describe('Préstamos', () => {
  async function abrirPrestamos(page: Page) {
    await page.goto(`${URL_PAGINAS}/Prestamos.php`);
  }

  function filaPrestamo(page: Page, id: number) {
    return filaConId(page.locator('#cuerpoTablaPrestamos'), id);
  }

  // DEFECTO: la tabla PRESTAMO no existe en la base (baseDeDatos/DDL/creacionPrestamo.sql no está aplicado), así que
  // el listado y el alta de préstamos responden 500 "Ocurrió un error, intente nuevamente." (DAOPrestamo.php:58 y :107).
  test('TEC-36 registrar un préstamo', async ({ page }) => {
    const equipo = await datos.crearEquipo();
    const fechaEstimada = sumarDias(hoyLocal(), 7);
    const listado = esperarRespuesta(page, 'prestamos.php', 'GET');
    await abrirPrestamos(page);
    expect((await listado).status(), 'el listado de préstamos debería cargar').toBe(200);
    await expect(page.locator('#mensajePrestamos')).toHaveText('');
    const opcionEquipo = page.locator(`#idEquipo option[value="${equipo.idEquipo}"]`);
    await expect(opcionEquipo).toBeAttached();

    await page.locator('#idEquipo').selectOption(equipo.idEquipo);
    await page.locator('#cedulaSolicitante').fill(CI_DOCENTE);
    await page.locator('#fechaDevolucionEstimada').fill(fechaEstimada);
    const respuestaPromesa = esperarRespuesta(page, 'prestamos.php', 'POST');
    await page.getByRole('button', { name: 'Registrar préstamo' }).click();
    const respuesta = await respuestaPromesa;

    expect(respuesta.status()).toBe(201);
    const prestamo = (await respuesta.json()).datos;
    datos.anotar('prestamos', prestamo.idPrestamo);

    await expect(page.locator('#mensajePrestamos')).toHaveText(`Préstamo ${prestamo.idPrestamo} registrado correctamente.`);
    await expect(filaPrestamo(page, prestamo.idPrestamo).locator('td')).toHaveText([
      String(prestamo.idPrestamo),
      equipo.idEquipo,
      `${CI_DOCENTE} - TEST_Docente TEST_Playwright`,
      /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/,
      fechaEstimada,
      '-',
      'Activo',
      'Registrar devolución',
    ]);

    // El equipo prestado deja de estar disponible.
    await expect(opcionEquipo).toHaveCount(0);
    expect((await datos.obtener('equipos', `id=${equipo.idEquipo}`)).disponibilidad).toBe('No disponible');
  });

  // DEFECTO: falta la tabla PRESTAMO en la base, el alta del préstamo responde 500 (DAOPrestamo.php:107).
  test('TEC-37 registrar la devolución de un préstamo y eliminarlo', async ({ page }) => {
    const equipo = await datos.crearEquipo();
    const prestamo = await datos.crear('prestamos', {
      idEquipo: equipo.idEquipo, cedulaSolicitante: CI_DOCENTE, fechaDevolucionEstimada: sumarDias(hoyLocal(), 3),
    }, 'idPrestamo');
    await abrirPrestamos(page);
    const fila = filaPrestamo(page, prestamo.idPrestamo);
    await expect(fila).toHaveCount(1);

    const devolucion = esperarRespuesta(page, 'prestamos.php', 'PUT');
    await fila.getByRole('button', { name: 'Registrar devolución' }).click();
    expect((await devolucion).status()).toBe(200);

    await expect(page.locator('#mensajePrestamos')).toHaveText(`Devolución del préstamo ${prestamo.idPrestamo} registrada correctamente.`);
    await expect(fila.locator('td').nth(6)).toHaveText('Devuelto');
    await expect(fila.locator('td').nth(5)).toHaveText(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/);
    expect((await datos.obtener('equipos', `id=${equipo.idEquipo}`)).disponibilidad).toBe('Disponible');

    await page.locator('#filtroEstadoPrestamo').selectOption('Activo');
    await expect(fila).toHaveCount(0);
    await page.locator('#filtroEstadoPrestamo').selectOption('Devuelto');
    await expect(fila).toHaveCount(1);

    const baja = esperarRespuesta(page, 'prestamos.php', 'DELETE');
    await fila.getByRole('button', { name: 'Eliminar' }).click();
    expect((await baja).status()).toBe(200);
    await expect(page.locator('#mensajePrestamos')).toHaveText(`Préstamo ${prestamo.idPrestamo} eliminado correctamente.`);
    await expect(fila).toHaveCount(0);
  });

  test('TEC-38 registrar un préstamo con datos inválidos desde la interfaz', async ({ page }) => {
    const equipo = await datos.crearEquipo();
    await abrirPrestamos(page);
    await expect(page.locator(`#idEquipo option[value="${equipo.idEquipo}"]`)).toBeAttached();
    const peticion = vigilarPeticion(page, 'prestamos.php', 'POST');

    await page.getByRole('button', { name: 'Registrar préstamo' }).click();
    expect(await campoVacio(page, '#idEquipo')).toBe(true);
    expect(await campoVacio(page, '#cedulaSolicitante')).toBe(true);
    expect(await campoVacio(page, '#fechaDevolucionEstimada')).toBe(true);

    await page.locator('#idEquipo').selectOption(equipo.idEquipo);
    await page.locator('#cedulaSolicitante').fill('12ab');
    await page.locator('#fechaDevolucionEstimada').fill(sumarDias(hoyLocal(), -1));
    await page.getByRole('button', { name: 'Registrar préstamo' }).click();
    expect(await page.locator('#cedulaSolicitante').evaluate((e: HTMLInputElement) => e.validity.patternMismatch)).toBe(true);
    expect(await page.locator('#fechaDevolucionEstimada').evaluate((e: HTMLInputElement) => e.validity.rangeUnderflow)).toBe(true);
    expect(peticion.enviada).toBe(false);

    // Cédula con formato válido pero sin usuario: lo rechaza el servidor.
    await page.locator('#cedulaSolicitante').fill('00000000');
    await page.locator('#fechaDevolucionEstimada').fill(sumarDias(hoyLocal(), 2));
    const respuesta = esperarRespuesta(page, 'prestamos.php', 'POST');
    await page.getByRole('button', { name: 'Registrar préstamo' }).click();
    expect((await respuesta).status()).toBe(400);
    await expect(page.locator('#mensajePrestamos')).toHaveText('No existe un usuario con la cédula 00000000.');
    expect((await datos.obtener('equipos', `id=${equipo.idEquipo}`)).disponibilidad).toBe('Disponible');
  });

  // DEFECTO: falta la tabla PRESTAMO en la base, el alta del préstamo válido responde 500 (DAOPrestamo.php:107).
  test('TEC-39 la API rechaza préstamos y devoluciones inválidos', async () => {
    const api = datos.api;
    const cabeceras = datos.cabeceras;
    const equipo = await datos.crearEquipo();
    const noDisponible = await datos.crearEquipo({ disponibilidad: 'No disponible' });
    const fecha = sumarDias(hoyLocal(), 2);

    const ocupado = await api.post(`${URL_API}/prestamos.php`, {
      headers: cabeceras, data: { idEquipo: noDisponible.idEquipo, cedulaSolicitante: CI_DOCENTE, fechaDevolucionEstimada: fecha },
    });
    expect(ocupado.status()).toBe(409);
    expect((await ocupado.json()).mensaje).toBe(`El equipo ${noDisponible.idEquipo} no está disponible para prestar.`);

    const pasada = await api.post(`${URL_API}/prestamos.php`, {
      headers: cabeceras, data: { idEquipo: equipo.idEquipo, cedulaSolicitante: CI_DOCENTE, fechaDevolucionEstimada: '2020-01-01' },
    });
    expect(pasada.status()).toBe(400);
    expect((await pasada.json()).mensaje).toBe('La fechaDevolucionEstimada no puede ser anterior a hoy.');

    const prestamo = await datos.crear('prestamos', { idEquipo: equipo.idEquipo, cedulaSolicitante: CI_DOCENTE, fechaDevolucionEstimada: fecha }, 'idPrestamo');
    const url = `${URL_API}/prestamos.php?id=${prestamo.idPrestamo}`;

    const borrarActivo = await api.delete(url, { headers: cabeceras });
    expect(borrarActivo.status()).toBe(409);

    expect((await api.put(url, { headers: cabeceras, data: { estado: 'Devuelto' } })).status()).toBe(200);
    const devolverDeNuevo = await api.put(url, { headers: cabeceras, data: { estado: 'Devuelto' } });
    expect(devolverDeNuevo.status()).toBe(409);
    expect((await devolverDeNuevo.json()).mensaje).toBe(`El préstamo ${prestamo.idPrestamo} ya fue devuelto.`);
  });
});

test.describe('Laboratorios', () => {
  /** Crea un laboratorio TEST_ (como técnico) y dos solicitudes en él (como docente). */
  async function prepararSolicitudes(request: APIRequestContext) {
    const marca = sufijo();
    const laboratorio = await datos.crear('laboratorios', { idLaboratorio: `TEST_LAB_${marca}`, numeroLaboratorio: `TEST_${marca}` }, 'idLaboratorio');

    await loginApi(request, USUARIOS.docente);
    const tokenDocente = await tokenApi(request);
    const crearSolicitud = async (datosSolicitud: object) => {
      const respuesta = await request.post(`${URL_API}/solicitudes.php`, {
        headers: { 'X-CSRF-Token': tokenDocente },
        data: { idLaboratorio: laboratorio.idLaboratorio, ...datosSolicitud },
      });
      expect(respuesta.status(), await respuesta.text()).toBe(201);
      const solicitud = (await respuesta.json()).datos;
      datos.anotar('solicitudes', solicitud.idSolicitud);
      return solicitud;
    };

    const conSoftware = await crearSolicitud({
      solicitaSoftware: true, detalle: 'TEST_ instalar Python', restricciones: 'TEST_ sin internet',
      fechaEstimada: sumarDias(hoyLocal(), 30), horaEstimada: '10:30',
    });
    const sinSoftware = await crearSolicitud({
      solicitaSoftware: false, fechaEstimada: sumarDias(hoyLocal(), 31), horaEstimada: '14:00',
    });
    return { laboratorio, conSoftware, sinSoftware };
  }

  test('TEC-40 ver las solicitudes de laboratorio', async ({ page, request }) => {
    const { laboratorio, conSoftware, sinSoftware } = await prepararSolicitudes(request);

    await page.goto(`${URL_PAGINAS}/VistaLab.php`);
    const cuerpo = page.locator('#cuerpoTabla');

    await expect(filaConId(cuerpo, conSoftware.idSolicitud).locator('td')).toHaveText([
      String(conSoftware.idSolicitud), laboratorio.numeroLaboratorio, 'Sí', 'TEST_ instalar Python', 'TEST_ sin internet',
      conSoftware.fechaEstimada, '10:30:00',
    ]);
    await expect(filaConId(cuerpo, sinSoftware.idSolicitud).locator('td')).toHaveText([
      String(sinSoftware.idSolicitud), laboratorio.numeroLaboratorio, 'No', '', '', sinSoftware.fechaEstimada, '14:00:00',
    ]);
  });

  test('TEC-41 filtrar las solicitudes por laboratorio y fecha', async ({ page, request }) => {
    const { laboratorio, conSoftware, sinSoftware } = await prepararSolicitudes(request);

    await page.goto(`${URL_PAGINAS}/VistaLab.php`);
    const cuerpo = page.locator('#cuerpoTabla');
    const filaSoftware = filaConId(cuerpo, conSoftware.idSolicitud);
    const filaSinSoftware = filaConId(cuerpo, sinSoftware.idSolicitud);
    const filasDeOtros = cuerpo.locator('tr').filter({ hasNot: page.locator('td', { hasText: laboratorio.numeroLaboratorio }) });

    await page.locator('#filtroPorLaboratorio').selectOption(laboratorio.numeroLaboratorio);
    await expect(filaSoftware).toBeVisible();
    await expect(filaSinSoftware).toBeVisible();
    for (const fila of await filasDeOtros.all()) {
      await expect(fila).toBeHidden();
    }

    await page.locator('#filtroPorFecha').fill(conSoftware.fechaEstimada);
    await expect(filaSoftware).toBeVisible();
    await expect(filaSinSoftware).toBeHidden();

    await page.getByRole('button', { name: 'Limpiar filtros' }).click();
    await expect(page.locator('#filtroPorFecha')).toHaveValue('');
    await expect(page.locator('#filtroPorLaboratorio')).toHaveValue('');
    await expect(filaSoftware).toBeVisible();
    await expect(filaSinSoftware).toBeVisible();
  });
});
