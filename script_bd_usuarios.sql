-- Seleccionar la base de datos
USE bd_tutorias;

-- Comprobar usuarios existentes
SELECT User, Host FROM mysql.user;

-- Crear usuario con contraseña segura
CREATE USER 'estudiante'@'localhost' IDENTIFIED BY '_-3KOEqZhM@mbYAX';

-- Dar permisos específicos sobre la tabla usuarios_1
GRANT UPDATE (nombre, apellido, correo, telefono, contrasena, ultima_modificacion) 
ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';

GRANT SELECT, INSERT, DELETE 
ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Mostrar permisos del nuevo usuario
SHOW GRANTS FOR 'estudiante'@'localhost';

