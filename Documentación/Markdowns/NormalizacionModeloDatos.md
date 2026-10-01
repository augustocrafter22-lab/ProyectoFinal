# NORMALIZACIÓN DEL MODELO DE DATOS

Este documento recorre cada tabla del DDL (`bd/DDL/`) y justifica por qué está en Tercera Forma Normal (3FN): todos sus atributos no clave dependen únicamente de la clave primaria completa, y no hay dependencias transitivas (un atributo no clave dependiendo de otro atributo no clave).

## Orden de ejecución del DDL

Los scripts de `bd/DDL/` tienen que ejecutarse en este orden, porque cada uno depende de que las tablas referenciadas por sus claves foráneas ya existan:

1. `creacionUsuario.sql`
2. `creacionLaboratorio.sql`
3. `creacionEquipo.sql`
4. `creacionTicket.sql`
5. `creacionDiagnosticoSolucion.sql`
6. `creacionReparacion.sql`
7. `creacionPrestamo.sql`

Antes de este issue, `creacionTicket.sql` no tenía ninguna clave foránea declarada, así que su orden no importaba. Al agregarle las FK hacia `LABORATORIO` y `EQUIPO` (ver más abajo), ahora sí es necesario ejecutarlo después de esos dos scripts.

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
* `laboratorio` también se agregó como FK a `LABORATORIO` (`fk_ticket_laboratorio`) por el mismo motivo.

**Denormalización deliberada #1 — `TICKET.laboratorio`:** este dato es técnicamente derivable desde `EQUIPO.idLaboratorio` (siguiendo `TICKET.equipo → EQUIPO.idLaboratorio`), por lo que en una 3FN estricta no debería repetirse en `TICKET`. Se mantiene así a propósito: representa el laboratorio donde ocurrió la incidencia *en el momento del ticket*, que puede no coincidir con el laboratorio actual del equipo si este se reubica después. Sacarlo de `TICKET` obligaría a reescribir todas las consultas que lo usan (fuera del alcance de este issue) y perdería ese dato histórico.

**Denormalización deliberada #2 — `TICKET.profesor`:** se guarda como texto libre (`VARCHAR(50)`) en vez de una FK a `DOCENTE`/`USUARIO.cedula`. Lo correcto en 3FN sería una FK, tal como ya hace `SOLICITUD_LABORATORIO.cedulaSolicitante`. Se documenta como deuda técnica conocida y no se corrige acá porque cambiarlo implica modificar el formulario de ingreso de tickets y todos los controladores que lo usan, lo cual excede el alcance de este issue (que pide documentar la normalización, no rediseñar el flujo de tickets).

## DIAGNOSTICO y SOLUCION

* **DIAGNOSTICO** (`idDiagnostico` PK): `idTicket` y `cedulaTecnico` son FK; `diagnostico` y `fechaDiagnostico` dependen solo de `idDiagnostico`.
* **SOLUCION** (`idSolucion` PK): `idDiagnostico` y `cedulaTecnico` son FK; `solucion` y `fechaSolucion` dependen solo de `idSolucion`.

## REPARACION

* **REPARACION** (`idReparacion` PK): `cedulaTecnico`, `reparacion` y `fechaReparacion` dependen solo de `idReparacion`.

**Denormalización deliberada #3 — `REPARACION.idTicket` e `idEquipo`:** ambos son derivables siguiendo `idDiagnostico → DIAGNOSTICO.idTicket → TICKET.equipo`, por lo que en 3FN estricta no deberían estar en `REPARACION`. Se mantienen como copia porque `AccesoDatosReparacion::registrarReparacion()` ya los completa automáticamente con un `INSERT...SELECT` desde esa misma cadena al crear el registro, evitando así un join de 3 tablas cada vez que se necesita filtrar reparaciones por ticket o por equipo (como hace `historialTecnico.php`). Es una denormalización típica para optimizar lectura, común cuando el dato de origen no cambia una vez creado el registro.

## PRESTAMO (nueva)

El DER del proyecto incluye una tabla `PRESTAMO` que no existía en el DDL. Se agrega en `bd/DDL/creacionPrestamo.sql`:

* **PRESTAMO** (`idPrestamo` PK): `idEquipo` y `cedulaSolicitante` son FK (a `EQUIPO` y `USUARIO` respectivamente); `fechaPrestamo`, `fechaDevolucionEstimada`, `fechaDevolucionReal` y `estado` dependen solo de `idPrestamo`. Sin dependencias transitivas.


