<?php
    require_once(__DIR__  .  "/Configuration.php");
    require_once(__DIR__  .  "/Response.php");

    date_default_timezone_set("America/Guatemala");

    class Connection extends PDO
    {
        public function __construct(string $rol)
        {
            switch ($rol) {
                case "3":
                    $usuario   = ADMIN_USER_DB;
                    $contrasena = ADMIN_PASSWORD_DB;
                    break;
                case "1":
                    $usuario   = ESTUDIANTE_USER_DB;
                    $contrasena = ESTUDIANTE_PASSWORD_DB;
                    break;
                case "2":
                    $usuario   = TUTOR_USER_DB;
                    $contrasena = TUTOR_PASSWORD_DB;
                    break;
                default:
                    $usuario   = NOTUSER_USER_DB;
                    $contrasena = NOTUSER_PASSWORD_DB;
                    break;
            }
            try {
                $dsn = "mysql:host=" . HOST_DB . ";dbname=" . DATABASE . ";charset=" . CHARSET;
                parent::__construct($dsn, $usuario, $contrasena);
                $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                Response::error("Ocurrio un error", -1001, 400);
                //Response::debug($e->getMessage(), -1001, 400);
            }
        }
    }
