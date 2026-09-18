-- ================================================================
-- SCRIPT UNICO: crea la base, las tablas, las vistas y los 3
-- usuarios de MySQL con sus privilegios. Correr esto UNA sola vez,
-- completo, en la pestana SQL de phpMyAdmin (no en el buscador de
-- una tabla especifica).
-- ================================================================

CREATE DATABASE IF NOT EXISTS bd_tutorias;
USE bd_tutorias;

-- ----------------------------------------------------------------
-- TABLAS SIN RELACIONES
-- ----------------------------------------------------------------

-- carreras con soft delete
CREATE TABLE IF NOT EXISTS carreras (
    id_carrera INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO carreras (nombre, descripcion) VALUES ('Ingenieria en sistemas','Carrera de ingenieria en sistemas');
INSERT INTO carreras (nombre, descripcion) VALUES ('Ingenieria industrial','Carrera de ingenieria industrial');
INSERT INTO carreras (nombre, descripcion) VALUES ('Derecho','Carrera de ciencias juridicas y sociales');

-- roles no hace soft delete
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL
);

INSERT INTO roles (nombre, descripcion) VALUES ('Estudiante','Usuario de estudiantes');
INSERT INTO roles (nombre, descripcion) VALUES ('Tutor','Usuario de tutores');
INSERT INTO roles (nombre, descripcion) VALUES ('Administrador','Usuario de Administradores');

-- ----------------------------------------------------------------
-- TABLAS CON RELACIONES
-- ----------------------------------------------------------------

-- materias con soft delete
CREATE TABLE IF NOT EXISTS materias (
    id_materia INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    id_carrera INT NOT NULL,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- usuarios con soft delete
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    telefono VARCHAR(9) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    id_rol INT NOT NULL,
    id_carrera INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ultima_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- vista solo para usuarios de estudiantes
CREATE OR REPLACE VIEW usuarios_1 AS SELECT * FROM usuarios WHERE id_rol = 1;

-- vista solo para usuarios de tutores
CREATE OR REPLACE VIEW usuarios_2 AS SELECT * FROM usuarios WHERE id_rol = 2;

-- vista solo para usuarios de administradores
CREATE OR REPLACE VIEW usuarios_3 AS SELECT * FROM usuarios WHERE id_rol = 3;

-- vista para el login compartido (todos los roles, columnas minimas)
-- usada por api/notuser/not_login.php, que aun no sabe el rol de quien entra
CREATE OR REPLACE VIEW usuarios_login AS
    SELECT id_usuario, correo, contrasena, id_rol, activo FROM usuarios;

-- tutorias ya tiene soft delete con el estado
CREATE TABLE IF NOT EXISTS tutorias (
    id_tutoria INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    estado VARCHAR(15) NOT NULL CHECK (estado IN ('activa','finalizada','cancelada','en curso')),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    id_tutor INT NOT NULL,
    id_materia INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ultima_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_fechas CHECK (fecha_fin > fecha_inicio),
    FOREIGN KEY (id_tutor) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- horarios ya tiene soft delete con el estado
CREATE TABLE IF NOT EXISTS horarios (
    id_horarios INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    dias_curso VARCHAR(255) NOT NULL CHECK (dias_curso IN ('lunes','martes','miercoles','jueves','viernes','sabado','domingo')),
    estado VARCHAR(15) NOT NULL CHECK (estado IN ('disponible','finalizado','cancelado','asignado')),
    id_tutoria INT NOT NULL,
    CONSTRAINT chk_horas CHECK (hora_fin > hora_inicio),
    FOREIGN KEY (id_tutoria) REFERENCES tutorias(id_tutoria)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- ----------------------------------------------------------------
-- TABLAS INTERMEDIAS
-- ----------------------------------------------------------------

-- solicitudes ya tiene soft delete con el estado
CREATE TABLE IF NOT EXISTS solicitudes (
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    estado VARCHAR(10) NOT NULL CHECK (estado IN ('pendiente','aprobada','rechazada')),
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    fecha_respuesta DATE NULL,
    id_horario INT NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_horario) REFERENCES horarios(id_horarios)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- TODO (nota de Javier): agregar tabla bitacora (id, fecha, info nueva,
-- info vieja en JSON) para auditoria de cambios. Aun no implementada.

-- ================================================================
-- USUARIOS DE MYSQL CON PRIVILEGIOS DISTINTOS POR ROL
-- ================================================================

-- ----------------------------------------------------------------
-- 1) Usuario ESTUDIANTE
-- ----------------------------------------------------------------
DROP USER IF EXISTS 'estudiante'@'localhost';
CREATE USER 'estudiante'@'localhost' IDENTIFIED BY '_-3KOEqZhM@mbYAX';

GRANT UPDATE (nombre, apellido, correo, telefono, id_carrera, contrasena, ultima_modificacion)
    ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';
GRANT SELECT, INSERT, DELETE ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';
GRANT SELECT ON bd_tutorias.carreras TO 'estudiante'@'localhost';

-- ----------------------------------------------------------------
-- 2) Usuario ADMINISTRADOR
-- ----------------------------------------------------------------
DROP USER IF EXISTS 'administrador'@'localhost';
CREATE USER 'administrador'@'localhost' IDENTIFIED BY '_dZDk)czYtmG16jk';

GRANT ALL PRIVILEGES ON bd_tutorias.* TO 'administrador'@'localhost';

-- ----------------------------------------------------------------
-- 3) Usuario NOTUSER (visitante sin sesion: solo el login compartido)
-- ----------------------------------------------------------------
DROP USER IF EXISTS 'notuser'@'localhost';
CREATE USER 'notuser'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_CLAVE_NOTUSER';

GRANT SELECT ON bd_tutorias.usuarios_login TO 'notuser'@'localhost';

-- ----------------------------------------------------------------
-- Aplicar todos los privilegios de una vez
-- ----------------------------------------------------------------
FLUSH PRIVILEGES;

-- ----------------------------------------------------------------
-- Verificacion final (revisa que las 3 salgan completas)
-- ----------------------------------------------------------------
SHOW GRANTS FOR 'estudiante'@'localhost';
SHOW GRANTS FOR 'administrador'@'localhost';
SHOW GRANTS FOR 'notuser'@'localhost';
