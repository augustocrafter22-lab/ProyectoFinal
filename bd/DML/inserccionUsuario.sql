INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
VALUES ('11111111', 'Administrador', 'Prueba', '$2y$12$LfM4s0M9fwK9lWdE1N/s.eBOACQ67SaAwAloGq.nL1dKZqHUSIOse', TRUE);

INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
VALUES ('22222222', 'Tecnico', 'Prueba', '$2y$12$pzEl5G4MdFVjxIRoJRslH.T7NPFNybFuktfm66vTs74VTaLJv2vnG', TRUE);

INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
VALUES ('33333333', 'Usuario', 'Prueba', '$2y$12$M/JJqXkOl4j70iqWBBE9k.f7YK7sTeI6jEh6.BAlAD6Aj0Nuchh7K', TRUE);

INSERT INTO USUARIO (cedula, nombre, apellido, clave, activo)
VALUES ('44444444', 'Docente', 'Prueba', '$2y$12$TNZLc7hRixyWKnv7ntOc9eWVrZ8kVxzXpLpipHBNXGUfDvBLtlzLS', TRUE);

-- Técnico
INSERT INTO TECNICO (cedula)
VALUES ('11111111');

-- Docente
INSERT INTO DOCENTE (cedula)
VALUES ('44444444');