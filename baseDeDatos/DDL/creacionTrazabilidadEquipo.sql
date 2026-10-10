CREATE TABLE HISTORIAL_ESTADO_EQUIPO (
    idHistorialEstado INT NOT NULL AUTO_INCREMENT,
    idEquipo VARCHAR(10) NOT NULL,
    estadoAnterior VARCHAR(30) NOT NULL,
    estadoNuevo VARCHAR(30) NOT NULL,
    cedulaUsuario CHAR(8) NOT NULL,
    fechaCambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_historial_estado_equipo
        PRIMARY KEY (idHistorialEstado),

    CONSTRAINT fk_historial_estado_equipo_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_historial_estado_equipo_usuario
        FOREIGN KEY (cedulaUsuario)
        REFERENCES USUARIO (cedula)
);

CREATE TABLE MOVIMIENTO_EQUIPO (
    idMovimiento INT NOT NULL AUTO_INCREMENT,
    idEquipo VARCHAR(10) NOT NULL,
    idLaboratorioAnterior VARCHAR(20) NOT NULL,
    idLaboratorioNuevo VARCHAR(20) NOT NULL,
    cedulaUsuario CHAR(8) NOT NULL,
    fechaMovimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_movimiento_equipo
        PRIMARY KEY (idMovimiento),

    CONSTRAINT fk_movimiento_equipo_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_movimiento_equipo_laboratorio_anterior
        FOREIGN KEY (idLaboratorioAnterior)
        REFERENCES LABORATORIO (idLaboratorio),

    CONSTRAINT fk_movimiento_equipo_laboratorio_nuevo
        FOREIGN KEY (idLaboratorioNuevo)
        REFERENCES LABORATORIO (idLaboratorio),

    CONSTRAINT fk_movimiento_equipo_usuario
        FOREIGN KEY (cedulaUsuario)
        REFERENCES USUARIO (cedula)
);

CREATE TABLE INTERVENCION (
    idIntervencion INT NOT NULL AUTO_INCREMENT,
    idEquipo VARCHAR(10) NOT NULL,
    tipo VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    cedulaTecnico CHAR(8) NOT NULL,
    fechaIntervencion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_intervencion
        PRIMARY KEY (idIntervencion),

    CONSTRAINT fk_intervencion_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_intervencion_tecnico
        FOREIGN KEY (cedulaTecnico)
        REFERENCES TECNICO (cedula)
);

CREATE TABLE REEMPLAZO (
    idReemplazo INT NOT NULL AUTO_INCREMENT,
    idEquipo VARCHAR(10) NOT NULL,
    componente VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    cedulaTecnico CHAR(8) NOT NULL,
    fechaReemplazo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_reemplazo
        PRIMARY KEY (idReemplazo),

    CONSTRAINT fk_reemplazo_equipo
        FOREIGN KEY (idEquipo)
        REFERENCES EQUIPO (idEquipo),

    CONSTRAINT fk_reemplazo_tecnico
        FOREIGN KEY (cedulaTecnico)
        REFERENCES TECNICO (cedula)
);