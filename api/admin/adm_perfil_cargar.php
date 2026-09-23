<?php
   
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'GET'){
        response::error("Metodo no permitido", -1000, 405);
    }

    
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3'){
        response::error("No autorizado", -1001, 401);
    }

    $id = $_SESSION['id'];

    try {
        
        $cn = new Connection("admin");

        
        $stmt = $cn->prepare("SELECT u.nombre, u.apellido, u.correo, u.telefono, u.contrasena, c.id_carrera AS idcarrera FROM usuarios_3 u INNER JOIN carreras c ON u.id_carrera = c.id_carrera WHERE u.id_usuario = ?");
       
        $stmt->execute([$id]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario == false){
            Response::error("Usuario no encontrado", -1002, 404);
        }

       
        Response::success("Datos obtenidos", 200, ["usuario"=>$usuario]);

    } catch (PDOException $error) {
        Response::error("No se pudieron carga los datos", -1003, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>