CREATE TABLE REPARACION (
    idReparacion INT NOT NULL AUTO_INCREMENT,
    idDiagnostico INT NOT NULL,
    cedulaTecnico CHAR(8) NOT NULL,
    reparacion TEXT NOT NULL,
    fechaReparacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_reparacion
        PRIMARY KEY (idReparacion),

    CONSTRAINT fk_reparacion_diagnostico
        FOREIGN KEY (idDiagnostico)
        REFERENCES DIAGNOSTICO (idDiagnostico),

    CONSTRAINT fk_reparacion_tecnico
        FOREIGN KEY (cedulaTecnico)
        REFERENCES TECNICO (cedula)
);
