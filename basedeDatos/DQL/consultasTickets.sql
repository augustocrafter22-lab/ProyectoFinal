-- Selecciona todos los tickets registrados, ordenados por fecha de creación
SELECT
    idTicket,
    laboratorio,
    equipo,
    asunto,
    descripcion,
    turno,
    grupo,
    profesor,
    estado,
    prioridad,
    fechaCreacion,
    fechaFinalizacion
FROM TICKET
ORDER BY fechaCreacion DESC;