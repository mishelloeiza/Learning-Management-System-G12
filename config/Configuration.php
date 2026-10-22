<?php

    require_once("Env.php");
    Env::load(__DIR__ . "/.env");

    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    define('COOKIE_NAME', Env::get('COOKIE_NAME'));
    define('COOKIE_KEY', Env::get('COOKIE_KEY'));

    //base de datos
    define("HOST_DB", Env::get('DATABASE_URL'));
    define("DATABASE", Env::get('DATABASE_NAME'));
    define("CHARSET", Env::get('DATABASE_CHARSET'));

    //usuario admin
    define("ADMIN_USER_DB", Env::get('ADMIN_DB_USER'));
    define("ADMIN_PASSWORD_DB", Env::get('ADMIN_DB_PSSW'));

    //usuario estudiante
    define("ESTUDIANTE_USER_DB", Env::get('ESTUDIANTE_DB_USER'));
    define("ESTUDIANTE_PASSWORD_DB", Env::get('ESTUDIANTE_DB_PSSW'));

    //usuario tutor
    define("TUTOR_USER_DB", Env::get('TUTOR_DB_USER'));
    define("TUTOR_PASSWORD_DB", Env::get('TUTOR_DB_PSSW'));

    //sin usuario
    define("NOTUSER_USER_DB", Env::get('NOTUSER_DB_USER'));
    define("NOTUSER_PASSWORD_DB", Env::get('NOTUSER_DB_PSSW'));
