INSERT INTO USUARIO (cedula, clave, activo)
VALUES ('11111111', 'clave123', TRUE);

INSERT INTO USUARIO (cedula, clave, activo)
VALUES ('22222222', 'clave456', TRUE);

INSERT INTO USUARIO (cedula, clave, activo)
VALUES ('33333333', 'clave789', TRUE);

INSERT INTO USUARIO (cedula, clave, activo)
VALUES ('44444444', 'clave444', TRUE);

-- Administrador
INSERT INTO ADMINISTRADOR (cedula)
VALUES ('11111111');

-- Técnico
INSERT INTO TECNICO (cedula)
VALUES ('22222222');

-- Docente
INSERT INTO DOCENTE (cedula)
VALUES ('44444444');

-- Usuario con ambos roles (Administrador y Técnico)
INSERT INTO TECNICO (cedula)
VALUES ('11111111');

DELETE FROM ADMINISTRADOR
WHERE cedula = '11111111';

DELETE FROM TECNICO
WHERE cedula = '22222222';

DELETE FROM DOCENTE
WHERE cedula = '44444444';

DELETE FROM USUARIO
WHERE cedula = '33333333';

UPDATE USUARIO
SET clave = 'nuevaClave'
WHERE cedula = '11111111';