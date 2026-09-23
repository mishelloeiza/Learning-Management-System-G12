<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $id_materia = trim($_POST["id_materia"] ?? '');

    if(empty($id_materia) || !ctype_digit($id_materia)) {
        Response::error("El id de la materia es obligatorio", -1002, 400);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("UPDATE materias SET activo = false WHERE id_materia = ?");
        $stmt->execute([$id_materia]);

        if($stmt->rowCount() === 0) {
            $existe = $cn->prepare("SELECT 1 FROM materias WHERE id_materia = ?");
            $existe->execute([$id_materia]);

            if(!$existe->fetchColumn()) {
                Response::error("No se encontro la materia", -1003, 404);
            }
        }

        Response::success("Materia eliminada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo eliminar la materia", -1004, 500);
    }
?>
