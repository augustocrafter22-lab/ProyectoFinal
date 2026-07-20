INSERT INTO USUARIO (cedula, clave, activo);
VALUES ('12345678', 'clave123', TRUE);
INSERT INTO USUARIO (cedula, clave, activo);
VALUES ('87654321', 'clave456', TRUE);
INSERT INTO USUARIO (cedula, clave, activo)
VALUES ('11223344', 'clave789', TRUE);

INSERT INTO ADMINISTRADOR (cedula)
VALUES ('12345678');
INSERT INTO TECNICO (cedula)
VALUES ('87654321');
INSERT INTO DOCENTE (cedula)
VALUES ('11223344');

DELETE FROM USUARIO 
WHERE cedula = {'una cedula'};

DELETE from ADMINISTRADOR
WHERE cedula = {'una cedula'};

DELETE from TECNICO
WHERE cedula = {'una cedula'};

DELETE from DOCENTE
WHERE cedula = {'una cedula'};

UPDATE USUARIO
SET clave = 'clave nueva'
WHERE cedula = 'cedula de usuario a modificar'