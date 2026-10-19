-- La fechaCreacion se indica para que los tickets ya resueltos o cerrados no queden
-- con una fechaFinalizacion anterior a su creación (el dashboard calcularía días negativos).
INSERT INTO TICKET (idTicket, laboratorio, equipo, asunto, descripcion, turno, grupo, profesor, estado, prioridad, fechaCreacion, fechaFinalizacion)
VALUES
('INC-2026-0001', 'LAB001', 'PC-03', 'Equipo requiere revisión', 'La PC no enciende correctamente, requiere revisión técnica.', 'Matutino', 'G1', 'Prof. Gómez', 'Pendiente', 'Alta', '2026-09-25 08:15:00', NULL),
('INC-2026-0002', 'LAB001', 'PC-04', 'Equipo dañado', 'El equipo está dañado y no responde.', 'Matutino', 'G2', 'Prof. Pérez', 'En Proceso', 'Media', '2026-09-26 09:40:00', NULL),
('INC-2026-0003', 'LAB002', 'PC-07', 'Pantalla dañada', 'La pantalla del equipo presenta fallas.', 'Vespertino', 'G3', 'Prof. Díaz', 'Resuelto', 'Alta', '2026-09-18 14:10:00', '2026-09-20 15:30:00'),
('INC-2026-0004', 'LAB002', 'PC-09', 'Equipo no inicia', 'El equipo no enciende al presionar el botón de inicio.', 'Vespertino', 'G1', 'Prof. Gómez', 'Pendiente', 'Indefinida', '2026-10-01 16:20:00', NULL),
('INC-2026-0005', 'LAB003', 'PC-12', 'Mantenimiento preventivo', 'Se requiere mantenimiento preventivo del equipo.', 'Nocturno', 'G4', 'Prof. Rodríguez', 'Cerrado', 'Baja', '2026-09-12 20:45:00', '2026-09-15 10:00:00');
