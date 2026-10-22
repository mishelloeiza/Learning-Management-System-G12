<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2' || !isset($_SESSION['id'])) {
        Response::error("No autorizado", -1001, 403);
    }

    $idTutor = $_SESSION['id'];
    $idTutoria = trim($_POST["id_tutoria"] ?? '');

    if($idTutoria === '' || !ctype_digit($idTutoria)) {
        Response::error("El id de la tutoria es obligatorio", -1180, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $cn->beginTransaction();

        $stmt = $cn->prepare("SELECT estado FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? FOR UPDATE");
        $stmt->execute([$idTutoria, $idTutor]);
        $estado = $stmt->fetchColumn();

        if($estado === false) {
            $cn->rollBack();
            Response::error("No se encontro la tutoria", -1181, 404);
        }

        if(!in_array($estado, ["activa", "en curso"], true)) {
            $cn->rollBack();
            Response::error("Solo se pueden eliminar tutorias activas o en curso", -1182, 409);
        }

        $cn->prepare("UPDATE tutorias SET estado = 'cancelada' WHERE id_tutoria = ?")->execute([$idTutoria]);
        $cn->prepare("UPDATE horarios SET estado = 'cancelado' WHERE id_tutoria = ? AND estado IN ('disponible','asignado')")->execute([$idTutoria]);
        $cn->prepare("UPDATE solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            SET s.estado = 'rechazada', s.fecha_respuesta = ? WHERE h.id_tutoria = ? AND s.estado = 'pendiente'")
            ->execute([date("Y-m-d"), $idTutoria]);

        $cn->commit();

        Response::success("Tutoria eliminada correctamente", 200);
    } catch (PDOException $error) {
        if(isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo eliminar la tutoria", -1183, 500);
    }
?>
