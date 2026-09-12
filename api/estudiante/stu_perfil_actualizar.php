<?php
    //Usar archivos de response y conexion
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1'){
        Response::error("No autorizado", -1001, 401);
    }

    $id = $_SESSION['id'];

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if(empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)){
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
        Response::error("Correo invalido", -1003, 400);
    }

    if(!ctype_digit($telefono) || strlen($telefono) > 9){
        Response::error("Telefono invalido", -1004, 400);
    }

    try {
        $cn = new Connection();

        $stmt = $cn->prepare("UPDATE usuarios_1 SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->execute([$nombre, $apellido, $correo, $telefono, $id]);

        Response::success("Informacion actualizada", 200);
    } catch (PDOException $error) {
        if(isset($error->errorInfo[1]) && $error->errorInfo[1] == 1062){
            Response::error("Ese correo ya fue registrado", -1005, 409);
        }
        Response::error("No se pudo actualizar la informacion", -1006, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>