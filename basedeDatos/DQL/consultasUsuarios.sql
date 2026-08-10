SELECT
    u.cedula,
    u.activo,
    CASE WHEN a.cedula IS NOT NULL THEN 1 ELSE 0 END AS coordinador,
    CASE WHEN t.cedula IS NOT NULL THEN 1 ELSE 0 END AS tecnico
FROM USUARIO u
LEFT JOIN ADMINISTRADOR a ON a.cedula = u.cedula
LEFT JOIN TECNICO t ON t.cedula = u.cedula
WHERE u.cedula = '44444444';

SELECT
    u.cedula,
    u.claveHash,
    u.activo,
    CASE WHEN a.cedula IS NOT NULL THEN 1 ELSE 0 END AS coordinador,
    CASE WHEN t.cedula IS NOT NULL THEN 1 ELSE 0 END AS tecnico
FROM USUARIO u
LEFT JOIN ADMINISTRADOR a ON a.cedula = u.cedula
LEFT JOIN TECNICO t ON t.cedula = u.cedula
WHERE u.cedula = :cedula;