<?php
    //Usar archivos de response y conexion
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    //Verificar que la conexion sea Get
    if($_SERVER['REQUEST_METHOD'] !== 'GET'){
        response::error("Metodo no permitido", -1000, 405);
    }

    //Verificar que si cumpla con el rol 1
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1'){
        response::error("No autorizado", -1001, 401);
    }

    $id = $_SESSION['id'];

    try {
        //Crear la conexion
        $cn = new Connection();

        //Hacer consulta a BDD
        $stmt = $cn->prepare("SELECT nombre, apellido, correo, telefono, contrasena FROM usuarios_1 WHERE id_usuario = ?"); 
        //Ejecutar con el id del usuario
        $stmt->execute([$id]);
        //Informacion del usuario como array
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        //Comprobar la informacion
        if($usuario == false){
            response::error("Usuario no encontrado", -1002, 404);
        }

        //Enviar los datos
        response::success("Datos obtenidos", 200, ["usuario"=>$usuario]);

    } catch (PDOException $error) {
        response::error("No se pudieron carga los datos", -1003, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>