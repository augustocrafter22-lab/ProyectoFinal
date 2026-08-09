/*
    Espacio donde se colocan las insercciones para testear Vista Tickets
*/

INSERT INTO TICKET (idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor, estado, prioridad, fechaCreacion, fechaFinalizacion) VALUES
    ('INC-2026-0001', 'Laboratorio 1', 'PC-03', 'La computadora no enciende', 'Al presionar el botón de encendido no responde ninguna luz ni sonido.', 'Matutino', '3MB', 'Marcela Mederos', 'Pendiente', 'Alta', '2026-08-01', NULL),
    ('INC-2026-0002', 'Laboratorio 2', 'PC-07', 'Pantalla azul al iniciar sesión', 'El equipo reinicia solo luego de iniciar sesión con el usuario del laboratorio.', 'Matutino', '3MB', 'Leandro Lopez', 'En Proceso', 'Media', '2026-08-02', NULL),
    ('INC-2026-0003', 'Taller 1', 'PC-12', 'Mouse no funciona', 'El mouse óptico no responde, se probó en otro puerto USB sin éxito.', 'Matutino', '3MB', 'Maria Garcia', 'Resuelto', 'Baja', '2026-08-03', '2026-08-04'),
    ('INC-2026-0004', 'Laboratorio 3', 'PC-01', 'No conecta a internet', 'El equipo no logra conectarse a la red del laboratorio de forma intermitente.', 'Matutino', '3MB', 'Ricardo Silva', 'Cerrado', 'Media', '2026-07-28', '2026-07-30');