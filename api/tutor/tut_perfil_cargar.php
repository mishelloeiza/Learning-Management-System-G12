<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'GET'){
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2'){
        Response::error("No autorizado", -1001, 401);
    }

    $id = $_SESSION['id'];

    try {
        $cn = new Connection("tutor");

        $stmt = $cn->prepare("SELECT nombre, apellido, correo, telefono, id_carrera AS idcarrera FROM usuarios_2 WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario == false){
            Response::error("Usuario no encontrado", -1002, 404);
        }

        Response::success("Datos obtenidos", 200, ["usuario"=>$usuario]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar los datos", -1003, 500);
    }
?>
