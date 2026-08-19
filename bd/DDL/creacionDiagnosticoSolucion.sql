CREATE TABLE DIAGNOSTICO (
    idDiagnostico INT NOT NULL AUTO_INCREMENT,
    idTicket VARCHAR(20) NOT NULL,
    cedulaTecnico CHAR(8) NOT NULL,
    diagnostico TEXT NOT NULL,
    fechaDiagnostico DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_diagnostico
        PRIMARY KEY (idDiagnostico),

    CONSTRAINT fk_diagnostico_ticket
        FOREIGN KEY (idTicket)
        REFERENCES TICKET (idTicket),

    CONSTRAINT fk_diagnostico_tecnico
        FOREIGN KEY (cedulaTecnico)
        REFERENCES TECNICO (cedula)
);