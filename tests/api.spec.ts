import { test, expect, request, APIRequestContext, APIResponse } from '@playwright/test';
import { BASE_URL, URL_API, URL_PAGINAS, USUARIOS, Usuario, loginApi, tokenApi } from './helpers';

type Rol = 'coordinador' | 'tecnico' | 'docente';
type Metodo = 'GET' | 'POST' | 'PUT' | 'DELETE';

const MENSAJE_SIN_SESION = 'Acceso denegado: sesión no iniciada.';
const MENSAJE_ROL = 'Acceso denegado: rol incorrecto.';
const MENSAJE_CSRF = 'Solicitud rechazada.';
const MENSAJE_METODO = 'Método no permitido.';

// Roles admitidos por cada método, según Sesion::verificarRolApi en cada Controlador*.php.
const C_T: Rol[] = ['coordinador', 'tecnico'];
const ENDPOINTS: Record<string, Record<Metodo, Rol[]>> = {
  diagnosticos: { GET: C_T, POST: C_T, PUT: C_T, DELETE: C_T },
  equipos: { GET: ['coordinador', 'tecnico', 'docente'], POST: C_T, PUT: C_T, DELETE: C_T },
  laboratorios: { GET: ['coordinador', 'tecnico', 'docente'], POST: C_T, PUT: C_T, DELETE: C_T },
  prestamos: { GET: C_T, POST: C_T, PUT: C_T, DELETE: C_T },
  reparaciones: { GET: C_T, POST: C_T, PUT: C_T, DELETE: C_T },
  solicitudes: { GET: C_T, POST: ['docente'], PUT: C_T, DELETE: C_T },
  soluciones: { GET: C_T, POST: C_T, PUT: C_T, DELETE: C_T },
  tickets: { GET: C_T, POST: ['coordinador', 'tecnico', 'docente'], PUT: C_T, DELETE: C_T },
  usuarios: { GET: ['coordinador'], POST: ['coordinador'], PUT: ['coordinador'], DELETE: ['coordinador'] },
};
const NOMBRES = Object.keys(ENDPOINTS);
const ROLES: Rol[] = ['coordinador', 'tecnico', 'docente'];
const ESCRITURA: Metodo[] = ['POST', 'PUT', 'DELETE'];
const TODOS: Metodo[] = ['GET', ...ESCRITURA];

// Página con meta csrf-token a la que puede entrar cada rol (el coordinador solo no tiene ninguna).
const PAGINA_TOKEN: Record<string, string> = { tecnico: 'VistaDeTickets.php', docente: 'IngresoDeTickets.php', principal: 'IngresoDeTickets.php' };

let numero = 0;
const id = () => `API-${String(++numero).padStart(2, '0')}`;

// Contextos de API con sesión, uno por usuario y por worker, para no loguear en cada test.
const contextos = new Map<string, Promise<APIRequestContext>>();
const tokens = new Map<string, Promise<string>>();

function sesion(clave: string, usuario: Usuario | null): Promise<APIRequestContext> {
  if (!contextos.has(clave)) {
    contextos.set(clave, (async () => {
      const contexto = await request.newContext({ baseURL: BASE_URL });
      if (usuario) {
        await loginApi(contexto, usuario);
      }
      return contexto;
    })());
  }
  return contextos.get(clave) as Promise<APIRequestContext>;
}

const anonimo = () => sesion('anonimo', null);
const conRol = (rol: Rol | 'principal') => sesion(rol, USUARIOS[rol]);

function tokenDe(rol: 'tecnico' | 'docente' | 'principal'): Promise<string> {
  if (!tokens.has(rol)) {
    tokens.set(rol, conRol(rol).then(contexto => tokenApi(contexto, PAGINA_TOKEN[rol])));
  }
  return tokens.get(rol) as Promise<string>;
}

test.afterAll(async () => {
  for (const contexto of contextos.values()) {
    await (await contexto).dispose();
  }
  contextos.clear();
  tokens.clear();
});

function url(endpoint: string, metodo: Metodo | 'PATCH'): string {
  // PUT y DELETE apuntan a un id que no existe, así nunca se modifica nada aunque pase el control.
  const consulta = metodo === 'PUT' || metodo === 'DELETE' ? '?id=TEST_NOEXISTE' : '';
  return `${URL_API}/${endpoint}.php${consulta}`;
}

function enviar(contexto: APIRequestContext, endpoint: string, metodo: Metodo | 'PATCH', headers: Record<string, string> = {}) {
  return contexto.fetch(url(endpoint, metodo), {
    method: metodo,
    headers,
    data: metodo === 'GET' ? undefined : {},
  });
}

async function esperarJson(respuesta: APIResponse, codigo: number, mensaje?: string) {
  expect(respuesta.status(), await respuesta.text()).toBe(codigo);
  expect(respuesta.headers()['content-type']).toContain('application/json');
  const cuerpo = await respuesta.json();
  expect(cuerpo.exito).toBe(codigo < 400);
  expect(typeof cuerpo.mensaje).toBe('string');
  if (mensaje) {
    expect(cuerpo.mensaje).toBe(mensaje);
  }
  return cuerpo;
}

// Un rol con permiso para el método, usando el que trae su propio token CSRF.
function rolPermitido(endpoint: string, metodo: Metodo): Rol {
  const roles = ENDPOINTS[endpoint][metodo];
  if (roles.includes('tecnico')) return 'tecnico';
  return roles[0];
}

test.describe('API sin sesión', () => {
  for (const metodo of TODOS) {
    for (const endpoint of NOMBRES) {
      test(`${id()} ${endpoint} ${metodo} sin sesión → 401`, async () => {
        const respuesta = await enviar(await anonimo(), endpoint, metodo);
        await esperarJson(respuesta, 401, MENSAJE_SIN_SESION);
      });
    }
  }
});

test.describe('API con rol incorrecto', () => {
  for (const endpoint of NOMBRES) {
    for (const metodo of TODOS) {
      for (const rol of ROLES.filter(r => !ENDPOINTS[endpoint][metodo].includes(r))) {
        test(`${id()} ${endpoint} ${metodo} como ${rol} → 403`, async () => {
          const respuesta = await enviar(await conRol(rol), endpoint, metodo);
          await esperarJson(respuesta, 403, MENSAJE_ROL);
        });
      }
    }
  }
});

test.describe('API GET con rol correcto', () => {
  for (const endpoint of NOMBRES) {
    for (const rol of ENDPOINTS[endpoint].GET) {
      // DEFECTO: prestamos GET responde 500 porque la tabla PRESTAMO no existe en la base
      // (DAOPrestamo.php:245-259 consulta PRESTAMO; el script baseDeDatos/DDL/creacionPrestamo.sql no está aplicado).
      test(`${id()} ${endpoint} GET como ${rol} → 200`, async () => {
        const respuesta = await (await conRol(rol)).get(`${URL_API}/${endpoint}.php`);
        const cuerpo = await esperarJson(respuesta, 200);
        expect(Array.isArray(cuerpo.datos)).toBe(true);
      });
    }
  }

  // Consultas con parámetros que usan las páginas del técnico (historial, diagnósticos de un ticket, filtros).
  const consultas: { ruta: string; rol: Rol; codigo: number }[] = [
    { ruta: 'reparaciones.php?idEquipo=TEST_NOEXISTE', rol: 'tecnico', codigo: 200 },
    { ruta: 'reparaciones.php?idEquipo=TEST_NOEXISTE', rol: 'docente', codigo: 403 },
    { ruta: 'diagnosticos.php?idTicket=TEST_NOEXISTE', rol: 'tecnico', codigo: 200 },
    { ruta: 'diagnosticos.php?idTicket=TEST_NOEXISTE', rol: 'docente', codigo: 403 },
    { ruta: 'tickets.php?estado=Pendiente', rol: 'tecnico', codigo: 200 },
    { ruta: 'tickets.php?estado=Pendiente', rol: 'docente', codigo: 403 },
    { ruta: 'solicitudes.php?tipo=software', rol: 'tecnico', codigo: 200 },
    { ruta: 'solicitudes.php?tipo=software', rol: 'docente', codigo: 403 },
    { ruta: 'equipos.php?disponibilidad=Disponible', rol: 'docente', codigo: 200 },
  ];
  for (const { ruta, rol, codigo } of consultas) {
    test(`${id()} GET ${ruta} como ${rol} → ${codigo}`, async () => {
      const respuesta = await (await conRol(rol)).get(`${URL_API}/${ruta}`);
      const cuerpo = await esperarJson(respuesta, codigo, codigo === 403 ? MENSAJE_ROL : undefined);
      if (codigo === 200) {
        expect(Array.isArray(cuerpo.datos)).toBe(true);
      }
    });
  }
});

test.describe('API escritura sin token CSRF válido', () => {
  const variantes: { caso: string; headers: Record<string, string> }[] = [
    { caso: 'sin X-CSRF-Token', headers: {} },
    { caso: 'con token inválido', headers: { 'X-CSRF-Token': 'token-invalido-0123456789abcdef' } },
  ];
  for (const { caso, headers } of variantes) {
    for (const endpoint of NOMBRES) {
      for (const metodo of ESCRITURA) {
        const rol = rolPermitido(endpoint, metodo);
        test(`${id()} ${endpoint} ${metodo} como ${rol} ${caso} → 403`, async () => {
          const respuesta = await enviar(await conRol(rol), endpoint, metodo, headers);
          await esperarJson(respuesta, 403, MENSAJE_CSRF);
        });
      }
    }
  }

  // Control: con el token correcto y un cuerpo vacío pasa los controles y lo rechaza la validación (400).
  const rolControl: Record<string, 'tecnico' | 'docente' | 'principal'> = { usuarios: 'principal', tickets: 'docente', solicitudes: 'docente' };
  for (const endpoint of NOMBRES) {
    const rol = rolControl[endpoint] ?? 'tecnico';
    test(`${id()} ${endpoint} POST como ${rol} con token válido y cuerpo vacío → 400`, async () => {
      const respuesta = await enviar(await conRol(rol), endpoint, 'POST', { 'X-CSRF-Token': await tokenDe(rol) });
      await esperarJson(respuesta, 400);
    });
  }

  test(`${id()} coordinador solo: su panel no expone el meta csrf-token`, async () => {
    const respuesta = await (await conRol('coordinador')).get(`${URL_PAGINAS}/Administrador.php`);
    expect(respuesta.url()).toContain('Administrador.php');
    expect(await respuesta.text()).not.toContain('name="csrf-token"');
  });
});

test.describe('API usuario inactivo', () => {
  test(`${id()} usuario inactivo no obtiene sesión`, async () => {
    const contexto = await sesion('inactivo', null);
    const respuesta = await contexto.post(`${URL_PAGINAS}/procesarLogin.php`, {
      form: { username: USUARIOS.inactivo.ci, clave: USUARIOS.inactivo.clave },
    });
    expect(respuesta.url()).toContain('Login.php?error=');
    await expect(loginApi(contexto, USUARIOS.inactivo)).rejects.toThrow();
  });

  for (const endpoint of NOMBRES) {
    test(`${id()} ${endpoint} GET tras intentar login como inactivo → 401`, async () => {
      const contexto = await sesion('inactivo', null);
      await contexto.post(`${URL_PAGINAS}/procesarLogin.php`, {
        form: { username: USUARIOS.inactivo.ci, clave: USUARIOS.inactivo.clave },
      });
      const respuesta = await contexto.get(`${URL_API}/${endpoint}.php`);
      await esperarJson(respuesta, 401, MENSAJE_SIN_SESION);
    });
  }
});

test.describe('API método no soportado', () => {
  for (const endpoint of NOMBRES) {
    const rol = endpoint === 'usuarios' ? 'coordinador' : 'tecnico';
    test(`${id()} ${endpoint} PATCH como ${rol} → 405`, async () => {
      const respuesta = await enviar(await conRol(rol), endpoint, 'PATCH');
      await esperarJson(respuesta, 405, MENSAJE_METODO);
    });
  }
});
