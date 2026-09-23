<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $id_tutoria = trim($_POST["id_tutoria"] ?? '');

    if(empty($id_tutoria) || !ctype_digit($id_tutoria)) {
        Response::error("El id de la tutoria es obligatorio", -1002, 400);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("UPDATE tutorias SET estado = 'cancelada' WHERE id_tutoria = ?");
        $stmt->execute([$id_tutoria]);

        if($stmt->rowCount() === 0) {
            $existe = $cn->prepare("SELECT 1 FROM tutorias WHERE id_tutoria = ?");
            $existe->execute([$id_tutoria]);

            if(!$existe->fetchColumn()) {
                Response::error("No se encontro la tutoria", -1003, 404);
            }
        }

        Response::success("Tutoria cancelada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo cancelar la tutoria", -1004, 500);
    }
?>
