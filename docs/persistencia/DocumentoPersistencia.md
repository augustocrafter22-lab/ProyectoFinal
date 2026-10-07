# DOCUMENTO DE PERSISTENCIA - TERCERA ENTREGA

Este documento releva todo lo que el sistema le pide a la base de datos. Tiene tres partes:

1. **Lista de consultas** (`SELECT`) que ejecuta el sistema.
2. **Resolución de cada consulta en álgebra relacional.**
3. **Lista de sentencias DML** (`INSERT`, `UPDATE` y `DELETE`).

Las consultas salen de las clases `DAO*` y `AccesoDatos*` de `PROYECTO/app/modelo/` y del archivo `baseDeDatos/DQL/accesoDatos.sql`. Las sentencias de datos iniciales están en `baseDeDatos/DML/`. Todas las consultas del sistema se ejecutan como consultas preparadas, con parámetros `:nombre`.

## Notación de álgebra relacional

* `π` proyección: elige columnas. Si una columna se calcula, es una proyección extendida.
* `σ` selección: elige filas que cumplen una condición.
* `⋈` junta (join) con la condición indicada abajo. Equivale a `INNER JOIN`.
* `⟕` junta externa izquierda. Equivale a `LEFT JOIN`: conserva las filas de la izquierda aunque no tengan pareja.
* `γ` agrupación y funciones de agregado (`COUNT`).
* `τ` ordenamiento (`↑` ascendente, `↓` descendente).
* `∧` y, `∨` o.

Las condiciones con un `:parámetro` se aplican con el valor que manda el usuario. Cuando un filtro es opcional, la condición solo se agrega si el filtro llega.

---

## 1. Lista de consultas del sistema

### USUARIO, ADMINISTRADOR, TECNICO, DOCENTE

* **Q1** - Buscar un usuario por cédula con su clave y sus roles. Se usa en el login. (`AccesoDatosUsuario::buscarUsuario`)
* **Q2** - Listar todos los usuarios con sus roles, ordenados por cédula. (`DAOUsuario::listar`, `AccesoDatosUsuario::obtenerTodos`)
* **Q3** - Obtener un usuario por cédula con sus roles, sin la clave. (`DAOUsuario::obtener`)
* **Q4** - Verificar si ya existe un usuario con una cédula. (`AltaDatosUsuario::usuarioExiste`)
* **Q5** - Cédulas de los usuarios activos. (`DQL`)
* **Q6** - Cédulas de los usuarios inactivos. (`DQL`)
* **Q7** - Buscar una cédula. (`DQL`)
* **Q8** - Buscar una cédula solo si el usuario está activo. (`DQL`)
* **Q9** - Buscar una cédula solo si el usuario está inactivo. (`DQL`)
* **Q10** - Cédulas de los usuarios que son administradores (coordinadores). (`DQL`)
* **Q11** - Cédulas de los usuarios que son técnicos. (`DQL`)
* **Q12** - Cédulas de los usuarios que son docentes. (`DQL`)

### TICKET

* **Q13** - Listar tickets, con filtros opcionales por estado, prioridad, rango de fechas y palabra clave, del más nuevo al más viejo. (`DAOTicket::listar`)
* **Q14** - Obtener un ticket por su id. (`DAOTicket::obtener`)
* **Q15** - Contar los tickets de un año para generar el próximo id (`INC-año-0001`). (`DAOTicket::generarIdTicket`)

### EQUIPO y LABORATORIO

* **Q16** - Listar equipos con el número de su laboratorio, con filtros opcionales por estado, laboratorio y disponibilidad. (`DAOEquipo::listar`)
* **Q17** - Obtener un equipo por su id con el número de su laboratorio. (`DAOEquipo::obtener`)
* **Q18** - Verificar que existe un laboratorio. (`DAOEquipo::existeLaboratorio`)
* **Q31** - Listar los laboratorios por número. (`AccesoDatosSolicitudLaboratorio::obtenerLaboratorios`)

### DIAGNOSTICO

* **Q19** - Listar diagnósticos con el equipo del ticket, opcionalmente de un solo ticket, del más nuevo al más viejo. (`DAODiagnostico::listar`)
* **Q20** - Obtener un diagnóstico por su id. (`DAODiagnostico::obtener`)
* **Q21** - Verificar que existe un ticket. (`DAODiagnostico::existeTicket`)

### SOLUCION

* **Q22** - Listar soluciones con su ticket y su equipo, de la más nueva a la más vieja. (`DAOSolucion::listar`)
* **Q23** - Obtener una solución por su id. (`DAOSolucion::obtener`)
* **Q24** - Verificar que existe un diagnóstico. (`DAOSolucion::existeDiagnostico`, `DAOReparacion::existeDiagnostico`)

### REPARACION

* **Q25** - Listar reparaciones, opcionalmente de un solo equipo, de la más nueva a la más vieja. (`DAOReparacion::listar`)
* **Q26** - Obtener una reparación por su id. (`DAOReparacion::obtener`)

### Dashboard (métricas de tickets)

* **Q27** - Cantidad de tickets por estado. (`AccesoDatosDashboard::contarPorEstado`)
* **Q28** - Cantidad total de tickets. (`AccesoDatosDashboard::contarTotal`)
* **Q29** - Tiempo de resolución, en días, de los tickets ya finalizados. (`AccesoDatosDashboard::obtenerTiemposResolucion`)
* **Q30** - Cantidad de incidencias por laboratorio, de mayor a menor. (`AccesoDatosDashboard::obtenerIncidenciasPorSalon`)

### SOLICITUD_LABORATORIO

* **Q32** - Listar las solicitudes de laboratorio con el número del laboratorio, por fecha y hora estimada. (`AccesoDatosSolicitudLaboratorio::obtenerSolicitudes`)

> La tabla `PRESTAMO` ya existe en el DDL, pero todavía no tiene consultas porque su módulo no está implementado.

---

## 2. Resolución en álgebra relacional

En las consultas de usuarios, `USUARIO ⟕ ADMINISTRADOR ⟕ TECNICO ⟕ DOCENTE` abrevia las tres juntas externas izquierdas por `cedula`. La columna `roles` (o `rol_admin`, `rol_tecnico` y `rol_docente`) es una proyección extendida: vale `coordinador`, `tecnico` o `docente` si la fila de esa tabla de rol existe, y vacío si no.

### Usuarios

```
Q1  π_{cedula, clave, nombre, apellido, activo, rol_admin, rol_tecnico, rol_docente}(
        σ_{cedula = :cedula}(
            ((USUARIO ⟕_{USUARIO.cedula = ADMINISTRADOR.cedula} ADMINISTRADOR)
                      ⟕_{USUARIO.cedula = TECNICO.cedula} TECNICO)
                      ⟕_{USUARIO.cedula = DOCENTE.cedula} DOCENTE))

Q2  τ_{cedula ↑}(
        π_{cedula, nombre, apellido, activo, roles}(
            USUARIO ⟕ ADMINISTRADOR ⟕ TECNICO ⟕ DOCENTE))

Q3  π_{cedula, nombre, apellido, activo, roles}(
        σ_{cedula = :cedula}(USUARIO ⟕ ADMINISTRADOR ⟕ TECNICO ⟕ DOCENTE))

Q4  π_{cedula}(σ_{cedula = :cedula}(USUARIO))

Q5  π_{cedula}(σ_{activo = TRUE}(USUARIO))

Q6  π_{cedula}(σ_{activo = FALSE}(USUARIO))

Q7  π_{cedula}(σ_{cedula = :cedula}(USUARIO))

Q8  π_{cedula}(σ_{cedula = :cedula ∧ activo = TRUE}(USUARIO))

Q9  π_{cedula}(σ_{cedula = :cedula ∧ activo = FALSE}(USUARIO))

Q10 π_{USUARIO.cedula}(USUARIO ⋈_{USUARIO.cedula = ADMINISTRADOR.cedula} ADMINISTRADOR)

Q11 π_{USUARIO.cedula}(USUARIO ⋈_{USUARIO.cedula = TECNICO.cedula} TECNICO)

Q12 π_{USUARIO.cedula}(USUARIO ⋈_{USUARIO.cedula = DOCENTE.cedula} DOCENTE)
```

### Tickets

```
Q13 τ_{fechaCreacion ↓}(
        π_{idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor,
           estado, prioridad, fechaCreacion, fechaFinalizacion}(
            σ_{estado = :estado ∧ prioridad = :prioridad
               ∧ fechaCreacion ≥ :fechaDesde ∧ fechaCreacion ≤ :fechaHasta
               ∧ (idTicket LIKE :q ∨ asunto LIKE :q ∨ descripcion LIKE :q)}(TICKET)))
    (cada condición de σ se agrega solo si ese filtro llegó)

Q14 π_{idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor,
       estado, prioridad, fechaCreacion, fechaFinalizacion}(
        σ_{idTicket = :idTicket}(TICKET))

Q15 γ_{COUNT(*)}(σ_{idTicket LIKE 'INC-año-%'}(TICKET))
```

### Equipos y laboratorios

```
Q16 τ_{idEquipo ↑}(
        π_{idEquipo, idLaboratorio, numeroLaboratorio → laboratorio, marca, estado,
           disponibilidad, informacion}(
            σ_{estado = :estado ∧ disponibilidad = :disponibilidad
               ∧ (EQUIPO.idLaboratorio LIKE :l ∨ numeroLaboratorio LIKE :l)}(
                EQUIPO ⋈_{EQUIPO.idLaboratorio = LABORATORIO.idLaboratorio} LABORATORIO)))
    (cada condición de σ se agrega solo si ese filtro llegó)

Q17 π_{idEquipo, idLaboratorio, numeroLaboratorio → laboratorio, marca, estado,
       disponibilidad, informacion}(
        σ_{idEquipo = :idEquipo}(
            EQUIPO ⋈_{EQUIPO.idLaboratorio = LABORATORIO.idLaboratorio} LABORATORIO))

Q18 γ_{COUNT(*)}(σ_{idLaboratorio = :idLaboratorio}(LABORATORIO))

Q31 τ_{numeroLaboratorio ↑}(π_{idLaboratorio, numeroLaboratorio, estado}(LABORATORIO))
```

### Diagnósticos, soluciones y reparaciones

```
Q19 τ_{fechaDiagnostico ↓}(
        π_{idDiagnostico, DIAGNOSTICO.idTicket, equipo, cedulaTecnico, diagnostico,
           fechaDiagnostico}(
            σ_{DIAGNOSTICO.idTicket = :idTicket}(
                DIAGNOSTICO ⋈_{DIAGNOSTICO.idTicket = TICKET.idTicket} TICKET)))
    (la σ se agrega solo si se pidió un ticket)

Q20 π_{idDiagnostico, DIAGNOSTICO.idTicket, equipo, cedulaTecnico, diagnostico,
       fechaDiagnostico}(
        σ_{idDiagnostico = :idDiagnostico}(
            DIAGNOSTICO ⋈_{DIAGNOSTICO.idTicket = TICKET.idTicket} TICKET))

Q21 γ_{COUNT(*)}(σ_{idTicket = :idTicket}(TICKET))

Q22 τ_{fechaSolucion ↓}(
        π_{idSolucion, SOLUCION.idDiagnostico, DIAGNOSTICO.idTicket, equipo,
           SOLUCION.cedulaTecnico, solucion, fechaSolucion}(
            (SOLUCION ⋈_{SOLUCION.idDiagnostico = DIAGNOSTICO.idDiagnostico} DIAGNOSTICO)
                      ⋈_{DIAGNOSTICO.idTicket = TICKET.idTicket} TICKET))

Q23 π_{idSolucion, SOLUCION.idDiagnostico, DIAGNOSTICO.idTicket, equipo,
       SOLUCION.cedulaTecnico, solucion, fechaSolucion}(
        σ_{idSolucion = :idSolucion}(
            (SOLUCION ⋈_{SOLUCION.idDiagnostico = DIAGNOSTICO.idDiagnostico} DIAGNOSTICO)
                      ⋈_{DIAGNOSTICO.idTicket = TICKET.idTicket} TICKET))

Q24 γ_{COUNT(*)}(σ_{idDiagnostico = :idDiagnostico}(DIAGNOSTICO))

Q25 τ_{fechaReparacion ↓}(
        π_{idReparacion, idDiagnostico, idTicket, idEquipo, cedulaTecnico, reparacion,
           fechaReparacion}(
            σ_{idEquipo = :idEquipo}(REPARACION)))
    (la σ se agrega solo si se pidió un equipo)

Q26 π_{idReparacion, idDiagnostico, idTicket, idEquipo, cedulaTecnico, reparacion,
       fechaReparacion}(
        σ_{idReparacion = :idReparacion}(REPARACION))
```

### Dashboard

```
Q27 γ_{estado; COUNT(*) → cantidad}(TICKET)

Q28 γ_{COUNT(*) → total}(TICKET)

Q29 τ_{fechaFinalizacion ↓}(
        π_{idTicket, DATEDIFF(fechaFinalizacion, fechaCreacion) → dias}(
            σ_{fechaFinalizacion ≠ NULL}(TICKET)))

Q30 τ_{cantidad ↓}(γ_{laboratorio; COUNT(*) → cantidad}(TICKET))
```

### Solicitudes de laboratorio

```
Q32 τ_{fechaEstimada ↑, horaEstimada ↑}(
        π_{idSolicitud, SOLICITUD_LABORATORIO.idLaboratorio, numeroLaboratorio,
           cedulaSolicitante, solicitaSoftware, detalle, restricciones, fechaEstimada,
           horaEstimada, fechaCreacion}(
            SOLICITUD_LABORATORIO
                ⋈_{SOLICITUD_LABORATORIO.idLaboratorio = LABORATORIO.idLaboratorio}
            LABORATORIO))
```

---

## 3. Lista de sentencias DML

Los `INSERT`, `UPDATE` y `DELETE` que ejecuta el sistema, agrupados por tabla. Cuando una operación toca varias tablas se ejecuta dentro de una transacción (`beginTransaction`, `commit` y `rollBack` si algo falla). Las operaciones de usuarios son las que usan transacción.

### USUARIO y roles (ADMINISTRADOR, TECNICO, DOCENTE)

Origen: `DAOUsuario` y `AltaDatosUsuario`. El alta, la edición y la baja son transaccionales.

```sql
-- Alta de usuario
INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
VALUES (:cedula, :nombre, :apellido, :clave, :activo);

-- Alta de cada rol del usuario (la tabla sale de una lista fija: coordinador -> ADMINISTRADOR,
-- tecnico -> TECNICO, docente -> DOCENTE)
INSERT INTO ADMINISTRADOR (cedula) VALUES (:cedula);
INSERT INTO TECNICO (cedula) VALUES (:cedula);
INSERT INTO DOCENTE (cedula) VALUES (:cedula);

-- Edición de usuario: solo se actualizan los campos que llegan (nombre, apellido, clave, activo)
UPDATE USUARIO SET nombre = :nombre, apellido = :apellido, clave = :clave, activo = :activo
WHERE cedula = :cedula;

-- Activar y desactivar
UPDATE USUARIO SET activo = 1 WHERE cedula = :cedula;
UPDATE USUARIO SET activo = 0 WHERE cedula = :cedula;

-- Cambio de roles: se quitan todos y se vuelven a insertar los nuevos
DELETE FROM ADMINISTRADOR WHERE cedula = :cedula;
DELETE FROM TECNICO WHERE cedula = :cedula;
DELETE FROM DOCENTE WHERE cedula = :cedula;

-- Baja de usuario (después de borrar sus roles)
DELETE FROM USUARIO WHERE cedula = :cedula;
```

### TICKET (`DAOTicket`)

```sql
INSERT INTO TICKET (idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor)
VALUES (:idTicket, :laboratorio, :equipo, :asunto, :descripcion, :turno, :grupo, :profesor);

-- Si el estado es Resuelto o Cerrado, fechaFinalizacion conserva la fecha que ya tenía o toma la actual;
-- con cualquier otro estado queda en NULL
UPDATE TICKET SET estado = :estado, prioridad = :prioridad, fechaFinalizacion = :fechaFinalizacion
WHERE idTicket = :idTicket;

DELETE FROM TICKET WHERE idTicket = :idTicket;
```

### EQUIPO (`DAOEquipo`)

```sql
INSERT INTO EQUIPO (idEquipo, idLaboratorio, marca, estado, disponibilidad, informacion)
VALUES (:idEquipo, :idLaboratorio, :marca, :estado, :disponibilidad, :informacion);

UPDATE EQUIPO
SET idLaboratorio = :idLaboratorio, marca = :marca, estado = :estado,
    disponibilidad = :disponibilidad, informacion = :informacion
WHERE idEquipo = :idEquipo;

DELETE FROM EQUIPO WHERE idEquipo = :idEquipo;
```

### DIAGNOSTICO (`DAODiagnostico`)

```sql
INSERT INTO DIAGNOSTICO (idTicket, cedulaTecnico, diagnostico)
VALUES (:idTicket, :cedulaTecnico, :diagnostico);

UPDATE DIAGNOSTICO SET diagnostico = :diagnostico WHERE idDiagnostico = :idDiagnostico;

DELETE FROM DIAGNOSTICO WHERE idDiagnostico = :idDiagnostico;
```

### SOLUCION (`DAOSolucion`)

```sql
INSERT INTO SOLUCION (idDiagnostico, cedulaTecnico, solucion)
VALUES (:idDiagnostico, :cedulaTecnico, :solucion);

UPDATE SOLUCION SET solucion = :solucion WHERE idSolucion = :idSolucion;

DELETE FROM SOLUCION WHERE idSolucion = :idSolucion;
```

### REPARACION (`DAOReparacion`)

```sql
-- El ticket y el equipo no los manda el usuario: salen del diagnóstico elegido
INSERT INTO REPARACION (idDiagnostico, idTicket, idEquipo, cedulaTecnico, reparacion)
SELECT d.idDiagnostico, d.idTicket, t.equipo, :cedulaTecnico, :reparacion
FROM DIAGNOSTICO AS d
INNER JOIN TICKET AS t ON t.idTicket = d.idTicket
WHERE d.idDiagnostico = :idDiagnostico;

UPDATE REPARACION SET reparacion = :reparacion WHERE idReparacion = :idReparacion;

DELETE FROM REPARACION WHERE idReparacion = :idReparacion;
```

El `SELECT` del `INSERT` de reparaciones, en álgebra relacional:

```
π_{DIAGNOSTICO.idDiagnostico, DIAGNOSTICO.idTicket, equipo}(
    σ_{DIAGNOSTICO.idDiagnostico = :idDiagnostico}(
        DIAGNOSTICO ⋈_{DIAGNOSTICO.idTicket = TICKET.idTicket} TICKET))
```

### SOLICITUD_LABORATORIO (`AccesoDatosSolicitudLaboratorio`)

```sql
INSERT INTO SOLICITUD_LABORATORIO
    (idLaboratorio, cedulaSolicitante, solicitaSoftware, detalle, restricciones, fechaEstimada, horaEstimada)
VALUES
    (:idLaboratorio, :cedulaSolicitante, :solicitaSoftware, :detalle, :restricciones, :fechaEstimada, :horaEstimada);
```

### Datos iniciales para pruebas (`baseDeDatos/DML/`)

* `inserccionUsuario.sql`: cuatro usuarios de prueba (uno de ellos sin rol) y los roles de los otros tres (`INSERT INTO USUARIO`, `ADMINISTRADOR`, `TECNICO` y `DOCENTE`).
* `inserccionLaboratorio.sql`: tres laboratorios (`INSERT INTO LABORATORIO`).
* `inserccionEquipo.sql`: dieciséis equipos repartidos en los tres laboratorios (`INSERT INTO EQUIPO`).
* `inserccionTicket.sql`: cinco tickets de ejemplo en distintos estados (`INSERT INTO TICKET`).

Se ejecutan después del DDL y en el mismo orden de dependencia: usuarios, laboratorios, equipos y tickets.

> `PRESTAMO` y la baja o edición de `SOLICITUD_LABORATORIO` y `LABORATORIO` todavía no tienen sentencias DML en el sistema.
