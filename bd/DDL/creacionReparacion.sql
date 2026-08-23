CREATE TABLE REPARACION (
    idReparacion INT NOT NULL AUTO_INCREMENT,
    idDiagnostico INT NOT NULL,
    idTicket VARCHAR(20) NOT NULL,
    idEquipo VARCHAR(10) NOT NULL,
    cedulaTecnico CHAR(8) NOT NULL,
    reparacion TEXT NOT NULL,
    fechaReparacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_reparacion
        PRIMARY KEY (idReparacion),

    CONSTRAINT fk_reparacion_diagnostico
        FOREIGN KEY (idDiagnostico)
        REFERENCES DIAGNOSTICO (idDiagnostico),

    CONSTRAINT fk_reparacion_ticket
        FOREIGN KEY (idTicket)
        REFERENCES TICKET (idTicket),

    CONSTRAINT fk_reparacion_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_reparacion_tecnico
        FOREIGN KEY (cedulaTecnico)
        REFERENCES TECNICO (cedula)
);