SELECT cedula 
FROM USUARIO
WHERE activo = TRUE;

SELECT cedula
FROM USUARIO
WHERE activo = FALSE;

SELECT cedula
FROM USUARIO
WHERE cedula = 'una cedula';

SELECT cedula
FROM USUARIO
WHERE cedula = 'una cedula' and activo = TRUE;

SELECT cedula
FROM USUARIO
WHERE cedula = 'una cedula' and activo = FALSE;

SELECT u.cedula
FROM USUARIO AS u
INNER JOIN ADMINISTRADOR AS a ON u.cedula = a.cedula;

SELECT u.cedula
FROM USUARIO AS u
INNER JOIN TECNICO AS t ON u.cedula = t.cedula;

SELECT u.cedula
FROM USUARIO AS u
INNER JOIN DOCENTE AS d ON u.cedula = d.cedula;

