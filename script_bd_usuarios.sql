-- Seleccionar la base de datos
USE bd_tutorias;

-- Comprobar usuarios existentes
SELECT User, Host FROM mysql.user;

-- Crear usuario de estudiantes
CREATE USER 'estudiante'@'localhost' IDENTIFIED BY '_-3KOEqZhM@mbYAX';
-- Dar permisos específicos sobre la vista usuarios_1
GRANT UPDATE (nombre, apellido, correo, telefono, contrasena, ultima_modificacion) ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';
GRANT SELECT, INSERT, DELETE ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';
-- Aplicar cambios
FLUSH PRIVILEGES;

-- Mostrar permisos del estudiante
SHOW GRANTS FOR 'estudiante'@'localhost';

