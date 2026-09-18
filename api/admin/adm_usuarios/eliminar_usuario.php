<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $id_usuario = trim($_POST["id_usuario"] ?? '');

    if(empty($id_usuario)) {
        Response::error("El id del usuario es obligarotio", -1002, 400);
    }

    try {
        $cn = new Connection();

        $stmt = $cn->prepare("UPDATE usuarios SET activo = false WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);

        if($stmt->rowCount() === 0) {
            Response::error("No se encontro el usuario", -1003, 404);
        }

        Response::success("Usuario eliminada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo eliminar al usuario", -1004, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>