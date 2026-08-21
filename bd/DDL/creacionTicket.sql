CREATE TABLE TICKET (
    idTicket VARCHAR(20) NOT NULL,
    laboratorio VARCHAR(20) NOT NULL,
    equipo VARCHAR(10) NOT NULL,
    asunto VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    turno VARCHAR(15) NOT NULL,
    grupo VARCHAR(10) NOT NULL,
    profesor VARCHAR(50) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
    prioridad VARCHAR(15) NOT NULL DEFAULT 'Indefinida',
    fechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fechaFinalizacion DATETIME NULL,

    CONSTRAINT pk_ticket
        PRIMARY KEY (idTicket)
);
