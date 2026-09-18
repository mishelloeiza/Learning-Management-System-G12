<?php
//Utilizar el archivo configuracion y response
require_once(__DIR__  .  "/Configuration.php");
require_once(__DIR__  .  "/Response.php");

//Establecer zona horaria
date_default_timezone_set("America/Guatemala");

//Clase conexion que usa propiedades de PDO
//Recibe el rol ("admin", "estudiante" o "notuser") y usa el usuario de MySQL
//correspondiente, definido en Configuration.php a partir del .env.
//Asi, aunque el codigo de conexion esta en un solo lugar, cada rol termina
//conectandose con un usuario real de MySQL distinto, con sus propios privilegios.
class Connection extends PDO
{
    public function __construct(string $rol)
    {
        switch ($rol) {
            case "admin":
                $usuario   = ADMIN_USER_DB;
                $contrasena = ADMIN_PASSWORD_DB;
                break;
            case "estudiante":
                $usuario   = ESTUDIANTE_USER_DB;
                $contrasena = ESTUDIANTE_PASSWORD_DB;
                break;
            case "notuser":
                $usuario   = NOTUSER_USER_DB;
                $contrasena = NOTUSER_PASSWORD_DB;
                break;
            default:
                Response::error("Rol de conexion invalido", -1000, 500);
                return;
        }

        try {
            //Crear la conexion con los parametros de configuration.php
            $dsn = "mysql:host=" . HOST_DB . ";dbname=" . DATABASE . ";charset=" . CHARSET;
            parent::__construct($dsn, $usuario, $contrasena);
            //Detectar si ocurre algun error en sql
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //Informacion de erorr de conexion
            Response::error("Ocurrio un error", -1001, 400);
            //Response::debug($e->getMessage(), -1001, 400);
        }
    }
}
