CREATE TABLE LABORATORIO (
    idLaboratorio VARCHAR(20) NOT NULL,
    numeroLaboratorio VARCHAR(10) NOT NULL,
    estado boolean NOT NULL DEFAULT TRUE,

    CONSTRAINT pk_laboratorio
        PRIMARY KEY (idLaboratorio)
);

// Solicitudes de laboratorio

CREATE TABLE SOLICITUD_LABORATORIO (
    idSolicitud INT NOT NULL AUTO_INCREMENT,
    idLaboratorio VARCHAR(20) NOT NULL,
    cedulaSolicitante CHAR(8) NOT NULL,
    solicitaSoftware BOOLEAN NOT NULL DEFAULT FALSE,
    detalle TEXT,
    restricciones TEXT,
    fechaEstimada DATE NOT NULL,
    horaEstimada TIME NOT NULL,
    fechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_solicitud_laboratorio
        PRIMARY KEY (idSolicitud),

    CONSTRAINT fk_solicitud_laboratorio_lab
        FOREIGN KEY (idLaboratorio)
        REFERENCES LABORATORIO (idLaboratorio),

    CONSTRAINT fk_solicitud_laboratorio_docente
        FOREIGN KEY (cedulaSolicitante)
        REFERENCES DOCENTE (cedula)
);