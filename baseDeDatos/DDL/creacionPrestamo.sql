CREATE TABLE PRESTAMO (
    idPrestamo INT NOT NULL AUTO_INCREMENT,
    idEquipo VARCHAR(10) NOT NULL,
    cedulaSolicitante CHAR(8) NOT NULL,
    fechaPrestamo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fechaDevolucionEstimada DATETIME NOT NULL,
    fechaDevolucionReal DATETIME NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Activo',

    CONSTRAINT pk_prestamo
        PRIMARY KEY (idPrestamo),

    CONSTRAINT fk_prestamo_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_prestamo_solicitante
        FOREIGN KEY (cedulaSolicitante)
        REFERENCES USUARIO (cedula)
);
