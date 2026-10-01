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

Antes de este issue, `creacionTicket.sql` no tenía ninguna clave foránea declarada, así que su orden no importaba. Al agregarle la FK hacia `EQUIPO` (ver más abajo), ahora sí es necesario ejecutarlo después de `creacionEquipo.sql` (que a su vez depende de `creacionLaboratorio.sql`).

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

## DIAGNOSTICO y SOLUCION

* **DIAGNOSTICO** (`idDiagnostico` PK): `idTicket` y `cedulaTecnico` son FK; `diagnostico` y `fechaDiagnostico` dependen solo de `idDiagnostico`.
* **SOLUCION** (`idSolucion` PK): `idDiagnostico` y `cedulaTecnico` son FK; `solucion` y `fechaSolucion` dependen solo de `idSolucion`.

## REPARACION

* **REPARACION** (`idReparacion` PK): `cedulaTecnico`, `reparacion` y `fechaReparacion` dependen solo de `idReparacion`.

## PRESTAMO (nueva)

El DER del proyecto incluye una tabla `PRESTAMO` que no existía en el DDL. Se agrega en `bd/DDL/creacionPrestamo.sql`:

* **PRESTAMO** (`idPrestamo` PK): `idEquipo` y `cedulaSolicitante` son FK (a `EQUIPO` y `USUARIO` respectivamente); `fechaPrestamo`, `fechaDevolucionEstimada`, `fechaDevolucionReal` y `estado` dependen solo de `idPrestamo`. Sin dependencias transitivas.


