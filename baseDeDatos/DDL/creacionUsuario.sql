CREATE TABLE USUARIO ( 
    cedula CHAR(8) NOT NULL,
    clave VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT pk_usuario
        PRIMARY KEY (cedula)
);

CREATE TABLE ADMINISTRADOR (
    cedula CHAR(8) NOT NULL,

    CONSTRAINT pk_administrador
        PRIMARY KEY (cedula)
);

CREATE TABLE TECNICO (
    cedula CHAR(8) NOT NULL,

    CONSTRAINT pk_tecnico
        PRIMARY KEY (cedula)
);

CREATE TABLE DOCENTE (
    cedula CHAR(8) NOT NULL,

    CONSTRAINT pk_docente
        PRIMARY KEY (cedula)
);

ALTER TABLE ADMINISTRADOR
    ADD CONSTRAINT fk_administrador_usuario
    FOREIGN KEY (cedula)
    REFERENCES USUARIO (cedula);

ALTER TABLE TECNICO
    ADD CONSTRAINT fk_tecnico_usuario
    FOREIGN KEY (cedula)
    REFERENCES USUARIO (cedula);

ALTER TABLE DOCENTE
    ADD CONSTRAINT fk_docente_usuario
    FOREIGN KEY (cedula)
    REFERENCES USUARIO (cedula);