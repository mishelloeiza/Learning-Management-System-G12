<?php
    //Usar archivos de response y conexion
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        Response::error("Metodo no permitido", -1000, 405);
    }

    $_SESSION = [];

    session_destroy(); 

    Response::success("Session cerrada correctamente", 200);
