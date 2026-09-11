-- usuarios 
use bd_tutorias;

-- comprobar usuarios
SELECT User, Host FROM mysql.user;

CREATE USER 'estudiante'@'localhost' IDENTIFIED BY '_-3KOEqZhM@mbYAX';
GRANT UPDATE (nombre, apellido, correo, telefono, contrasena, ultima_modificacion) ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost';
GRANT SELECT, INSERT, DELETE ON bd_tutorias.usuarios_1 TO 'estudiante'@'localhost'; 
FLUSH PRIVILEGES;

SHOW GRANTS FOR 'estudiante'@'localhost';
