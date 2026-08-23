CREATE TABLE EQUIPO (
    idEquipo VARCHAR(10) NOT NULL,
    idLaboratorio VARCHAR(20) NOT NULL,
    marca VARCHAR(30) NOT NULL,
    estado VARCHAR(30) NOT NULL,
    disponibilidad VARCHAR(30) NOT NULL,
    informacion TEXT,

    CONSTRAINT pk_equipo
        PRIMARY KEY (idEquipo),

    CONSTRAINT fk_equipo_laboratorio
        FOREIGN KEY (idLaboratorio)
        REFERENCES LABORATORIO (idLaboratorio)
);
