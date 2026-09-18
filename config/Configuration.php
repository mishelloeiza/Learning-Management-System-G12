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

define("HOST_DB", Env::get('DATABASE_URL'));
define("USER_DB", Env::get('DATABASE_USER'));
define("PASSWORD_DB", Env::get('DATABASE_PSSW'));
define("DATABASE", Env::get('DATABASE_NAME'));
define("CHARSET", Env::get('DATABASE_CHARSET'));
