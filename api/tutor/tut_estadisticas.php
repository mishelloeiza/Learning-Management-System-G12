<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1420, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "2" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1421, 403);
    }

    $idTutor = $_SESSION["id"];

    try {
        $cn = new Connection($_SESSION["rol"]);
        $hoy = date("Y-m-d");

        // Mismo ajuste de estados que ver_en_curso.php, para que los conteos no queden desfasados
        $cn->prepare("UPDATE tutorias SET estado = 'en curso' WHERE id_tutor = ? AND estado = 'activa' AND ? BETWEEN fecha_inicio AND fecha_fin")
            ->execute([$idTutor, $hoy]);
        $cn->prepare("UPDATE tutorias SET estado = 'finalizada' WHERE id_tutor = ? AND estado IN ('activa','en curso') AND fecha_fin < ?")
            ->execute([$idTutor, $hoy]);

        $stmt = $cn->prepare("SELECT
            (SELECT COUNT(*) FROM tutorias WHERE id_tutor = ?) AS total,
            (SELECT COUNT(*) FROM tutorias WHERE id_tutor = ? AND estado = 'activa') AS activas,
            (SELECT COUNT(*) FROM tutorias WHERE id_tutor = ? AND estado = 'en curso') AS en_curso,
            (SELECT COUNT(*) FROM tutorias WHERE id_tutor = ? AND estado = 'finalizada') AS finalizadas,
            (SELECT COUNT(*) FROM solicitudes s
                INNER JOIN horarios h ON s.id_horario = h.id_horarios
                INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
                WHERE t.id_tutor = ? AND s.estado = 'pendiente') AS solicitudes_pendientes,
            (SELECT COUNT(DISTINCT s.id_usuario) FROM solicitudes s
                INNER JOIN horarios h ON s.id_horario = h.id_horarios
                INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
                WHERE t.id_tutor = ? AND s.estado = 'aprobada') AS estudiantes");
        $stmt->execute([$idTutor, $idTutor, $idTutor, $idTutor, $idTutor, $idTutor]);

        Response::success("Estadisticas cargadas", 200, ["estadisticas" => $stmt->fetch(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las estadisticas", -1422, 500);
    }
?>
