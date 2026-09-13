<?php
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        Response::error("Metodo no permitido", -1000, 404);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1'){
        Response::error("No autorizado", -1001, 404);
    }

    $id = $_SESSION['id'];

    $contrasena_actu = trim($_POST['contrasena_act'] ?? '');
    $contrasena_new = trim($_POST['contrasena_new'] ?? '');

    if(empty($contrasena_actu) || empty($contrasena_new)){
        Response::error("Las contraseñas son obligatorias", -1002, 400);
    }

    if(strlen($contrasena_new) < 8){
        Response::error("La nueva contraseña debe tenener al menos 8 caracteres", -1003, 400);
    }

    try {
        $cn = new Connection();

        $stmt = $cn->prepare('SELECT contrasena FROM usuarios_1 WHERE id_usuario = ?');
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario === false || !password_verify($contrasena_actu, $usuario['contrasena'])){
            Response::error("La contraseña actual es incorrecta", -1004, 404);
        }

        $cifrada = password_hash($contrasena_new, PASSWORD_DEFAULT);

        $stmt = $cn->prepare('UPDATE usuarios_1 SET contrasena = ? WHERE id_usuario = ?');
        $stmt->execute([$cifrada, $id]);

        Response::success("Contraseña actualizada correctamente", 200);

    } catch (PDOException $error) {
        Response::error("No se pudo actualizar la contraseña", -1005, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }

?>