SELECT cedula, claveHash, activo
FROM USUARIO
WHERE cedula = '11111111';

-- Ve si la cédula tiene rol administrador
SELECT cedula
FROM ADMINISTRADOR
WHERE cedula = '11111111';

-- Ve si la cédula tiene rol tecnico 
SELECT cedula
FROM TECNICO
WHERE cedula = '11111111';

-- Para lisar los usuarios con los roles que tienen (administrador, tecnico o ninguno)
SELECT
    u.cedula,
    u.activo,
    CASE WHEN a.cedula IS NOT NULL THEN 1 ELSE 0 END AS esAdministrador,
    CASE WHEN t.cedula IS NOT NULL THEN 1 ELSE 0 END AS esTecnico
FROM USUARIO u
LEFT JOIN ADMINISTRADOR a ON a.cedula = u.cedula
LEFT JOIN TECNICO t ON t.cedula = u.cedula;
-- LEFT JOIN es para no perder a los que no tienen ningún rol

SELECT cedula, claveHash, activo
FROM USUARIO
WHERE cedula = :cedula;


SELECT cedula
FROM ADMINISTRADOR
WHERE cedula = :cedula;


SELECT cedula
FROM TECNICO
WHERE cedula = :cedula;