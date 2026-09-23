<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $id_carrera = trim($_POST["id_carrera"] ?? '');

    if(empty($id_carrera)) {
        Response::error("El id de la carrera es obligarotio", -1002, 400);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("UPDATE carreras SET activo = false WHERE id_carrera = ?");
        $stmt->execute([$id_carrera]);

        if($stmt->rowCount() === 0) {
            Response::error("No se encontro la carrera", -1003, 404);
        }

        Response::success("Carrera eliminada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo eliminar la carrera", -1004, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>