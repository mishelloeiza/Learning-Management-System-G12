<?php
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1'){
        Response::error("No autorizado", -1001, 404);
    }

    $id = $_SESSION['id'];

    try {
        $cn = new Connection();
        $stmt = $cn->prepare("DELETE FROM usuarios_1 WHERE id_usuario = ?");
        $stmt->execute([$id]);

        if($stmt->rowCount() === 0){
            Response::error("La cuenta no existe", -1002, 404);
        }

        $_SESSION = [];
        session_destroy();

        Response::success("Cuenta Eliminada", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo eliminar la cuenta", -1003, 500);
    }
?>