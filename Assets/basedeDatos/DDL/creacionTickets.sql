/*
    Para crear la tabla de tickets.
*/

CREATE TABLE TICKET (
    idTicket CHAR(13) NOT NULL,
    laboratorio VARCHAR(50) NOT NULL,
    equipo VARCHAR(20) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    turno VARCHAR(20) NOT NULL,
    grupo VARCHAR(20) NOT NULL,
    profesor VARCHAR(100) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
    prioridad VARCHAR(20) NOT NULL DEFAULT 'Indefinida',
    fechaCreacion DATE NOT NULL,
    fechaFinalizacion DATE NULL,

    CONSTRAINT pk_ticket
        PRIMARY KEY (idTicket)
);