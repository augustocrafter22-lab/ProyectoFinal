# NORMALIZACIÓN DEL MODELO DE DATOS

Este documento recorre cada tabla del DDL (`baseDeDatos/DDL/`) y justifica por qué está en Tercera Forma Normal (3FN): todos sus atributos no clave dependen únicamente de la clave primaria completa, y no hay dependencias transitivas (un atributo no clave dependiendo de otro atributo no clave).

## Orden de ejecución del DDL

Los scripts de `baseDeDatos/DDL/` tienen que ejecutarse en este orden, porque cada uno depende de que las tablas referenciadas por sus claves foráneas ya existan:

1. `creacionUsuario.sql`
2. `creacionLaboratorio.sql`
3. `creacionEquipo.sql`
4. `creacionTicket.sql`
5. `creacionDiagnosticoSolucion.sql`
6. `creacionReparacion.sql`
7. `creacionPrestamo.sql`
8. `creacionTrazabilidadEquipo.sql`

Antes de este issue, `creacionTicket.sql` no tenía ninguna clave foránea declarada, así que su orden no importaba. Al agregarle la FK hacia `EQUIPO` (ver más abajo), ahora sí es necesario ejecutarlo después de `creacionEquipo.sql` (que a su vez depende de `creacionLaboratorio.sql`).

Los datos de prueba de `baseDeDatos/DML/` se cargan después, en el mismo orden de dependencia: `inserccionUsuario.sql`, `inserccionLaboratorio.sql`, `inserccionEquipo.sql` e `inserccionTicket.sql`.

## USUARIO, ADMINISTRADOR, TECNICO, DOCENTE

* **USUARIO** (`cedula` PK): `nombre`, `apellido`, `clave` y `activo` dependen solo de `cedula`. Sin dependencias transitivas.
* **ADMINISTRADOR**, **TECNICO**, **DOCENTE** (`cedula` PK/FK a USUARIO): cada rol es una tabla de extensión 1 a 1. Esto evita guardar los roles como una columna con una lista separada por comas (lo cual violaría 1FN) o como columnas booleanas sueltas en USUARIO (lo cual mezclaría datos de usuario con datos de rol).

## LABORATORIO y SOLICITUD_LABORATORIO

* **LABORATORIO** (`idLaboratorio` PK): `numeroLaboratorio` y `estado` dependen solo de la clave.
* **SOLICITUD_LABORATORIO** (`idSolicitud` PK): todos sus atributos (`idLaboratorio`, `cedulaSolicitante`, `solicitaSoftware`, `detalle`, `restricciones`, `fechaEstimada`, `horaEstimada`, `fechaCreacion`) dependen solo de `idSolicitud`. Las referencias a `LABORATORIO` y `DOCENTE` son claves foráneas, no datos duplicados.

## EQUIPO

* **EQUIPO** (`idEquipo` PK): `idLaboratorio`, `marca`, `estado`, `disponibilidad` e `informacion` dependen solo de `idEquipo`.

## TICKET

* **TICKET** (`idTicket` PK): `asunto`, `descripcion`, `turno`, `grupo`, `estado`, `prioridad`, `fechaCreacion` y `fechaFinalizacion` dependen solo de `idTicket`.
* `equipo` es FK a `EQUIPO`. Antes no tenía la restricción `FOREIGN KEY` declarada en el DDL aunque el código la trataba como tal (joins en `AccesoDatosDashboard`, `AccesoDatosReparacion`, etc.) — se agregó la constraint `fk_ticket_equipo` para que la base de datos la garantice, no solo el código PHP.
* `laboratorio` no es una dependencia transitiva de `equipo`: un equipo puede cambiar de laboratorio (queda registrado en `MOVIMIENTO_EQUIPO`), y el ticket tiene que recordar en qué laboratorio ocurrió la incidencia aunque el equipo se mueva después. Por eso `laboratorio` depende del ticket y no del equipo. Al crear el ticket, el sistema verifica que el equipo esté en ese laboratorio (`DAOTicket::equipoEstaEnLaboratorio`).

## DIAGNOSTICO y SOLUCION

* **DIAGNOSTICO** (`idDiagnostico` PK): `idTicket` y `cedulaTecnico` son FK; `diagnostico` y `fechaDiagnostico` dependen solo de `idDiagnostico`.
* **SOLUCION** (`idSolucion` PK): `idDiagnostico` y `cedulaTecnico` son FK; `solucion` y `fechaSolucion` dependen solo de `idSolucion`.

## REPARACION

* **REPARACION** (`idReparacion` PK): `idDiagnostico` y `cedulaTecnico` son FK; `reparacion` y `fechaReparacion` dependen solo de `idReparacion`.
* En la integración final (issue #221) se quitaron las columnas `idTicket` e `idEquipo` de REPARACION. Las dos se podían deducir de `idDiagnostico` (`idDiagnostico → idTicket` por DIAGNOSTICO, e `idTicket → equipo` por TICKET), o sea que eran dependencias transitivas y dejaban la tabla fuera de 3FN: el mismo dato quedaba guardado dos veces y podía contradecirse (por ejemplo, una reparación con un equipo distinto al del ticket de su diagnóstico). Ahora el ticket y el equipo de una reparación se obtienen con un join `REPARACION ⋈ DIAGNOSTICO ⋈ TICKET` (`DAOReparacion`, `DAOHistorialEquipo`), y la API devuelve los mismos datos que antes.

## HISTORIAL_ESTADO_EQUIPO, MOVIMIENTO_EQUIPO, INTERVENCION y REEMPLAZO

Son las tablas de trazabilidad de `creacionTrazabilidadEquipo.sql`. Cada fila es un hecho que pasó una vez, por eso guardan el valor anterior y el nuevo en lugar de copiarlo de EQUIPO.

* **HISTORIAL_ESTADO_EQUIPO** (`idHistorialEstado` PK): `idEquipo` y `cedulaUsuario` son FK; `estadoAnterior`, `estadoNuevo` y `fechaCambio` dependen solo de la clave.
* **MOVIMIENTO_EQUIPO** (`idMovimiento` PK): `idEquipo`, `idLaboratorioAnterior`, `idLaboratorioNuevo` y `cedulaUsuario` son FK; `fechaMovimiento` depende solo de la clave.
* **INTERVENCION** (`idIntervencion` PK): `idEquipo` y `cedulaTecnico` son FK; `tipo`, `descripcion` y `fechaIntervencion` dependen solo de la clave.
* **REEMPLAZO** (`idReemplazo` PK): `idEquipo` y `cedulaTecnico` son FK; `componente`, `descripcion` y `fechaReemplazo` dependen solo de la clave.

## PRESTAMO 

El DER del proyecto incluye una tabla `PRESTAMO` que no existía en el DDL. Se agrega en `baseDeDatos/DDL/creacionPrestamo.sql`:

* **PRESTAMO** (`idPrestamo` PK): `idEquipo` y `cedulaSolicitante` son FK (a `EQUIPO` y `USUARIO` respectivamente); `fechaPrestamo`, `fechaDevolucionEstimada`, `fechaDevolucionReal` y `estado` dependen solo de `idPrestamo`. 

