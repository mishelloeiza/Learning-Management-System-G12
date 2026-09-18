<?php
//Utilizar el archivo env.php y el .env
require_once("Env.php");
Env::load(__DIR__ . "/.env");

//Crear y proteger la cookie
//Identificar el usuario y mantener su informacion mientras use la pagina
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

//Verificar si existe una sesion y si no existe crearla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

#Definir las variables de cookie y bdd
define('COOKIE_NAME', Env::get('COOKIE_NAME'));
define('COOKIE_KEY', Env::get('COOKIE_KEY'));

// Datos compartidos por los 3 usuarios de MySQL: mismo servidor, misma base, mismo charset.
// Lo unico que cambia entre admin/estudiante/notuser es el USUARIO y CONTRASENA de MySQL,
// porque cada uno tiene privilegios distintos definidos con GRANT en la base de datos.
define("HOST_DB", Env::get('DATABASE_URL'));
define("DATABASE", Env::get('DATABASE_NAME'));
define("CHARSET", Env::get('DATABASE_CHARSET'));

// Credenciales de MySQL para el rol "admin" (privilegios completos)
define("ADMIN_USER_DB", Env::get('ADMIN_DB_USER'));
define("ADMIN_PASSWORD_DB", Env::get('ADMIN_DB_PSSW'));

// Credenciales de MySQL para el rol "estudiante" (privilegios limitados)
define("ESTUDIANTE_USER_DB", Env::get('ESTUDIANTE_DB_USER'));
define("ESTUDIANTE_PASSWORD_DB", Env::get('ESTUDIANTE_DB_PSSW'));

// Credenciales de MySQL para visitantes sin sesion (login, crear cuenta): los mas restringidos
define("NOTUSER_USER_DB", Env::get('NOTUSER_DB_USER'));
define("NOTUSER_PASSWORD_DB", Env::get('NOTUSER_DB_PSSW'));
