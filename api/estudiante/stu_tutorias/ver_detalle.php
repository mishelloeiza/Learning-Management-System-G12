<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1320, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1321, 403);
    }
    $idTutoria = trim($_GET["id_tutoria"] ?? "");
    if ($idTutoria === "" || !ctype_digit($idTutoria)) {
        Response::error("Tutoria invalida", -1322, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $stmt = $cn->prepare("SELECT t.id_tutoria, t.titulo, t.descripcion, t.estado, t.fecha_inicio, t.fecha_fin,
            CONCAT(u.nombre, ' ', u.apellido) AS tutor, m.nombre AS materia,
            (SELECT s.estado FROM solicitudes s INNER JOIN horarios hs ON s.id_horario = hs.id_horarios
                WHERE hs.id_tutoria = t.id_tutoria AND s.id_usuario = ?
                ORDER BY FIELD(s.estado, 'rechazada', 'aprobada', 'pendiente') LIMIT 1) AS estado_solicitud
            FROM tutorias t INNER JOIN usuarios_2 u ON t.id_tutor = u.id_usuario
            INNER JOIN materias m ON t.id_materia = m.id_materia
            WHERE t.id_tutoria = ? AND t.estado IN ('activa','en curso') AND t.fecha_fin >= CURDATE()");
        $stmt->execute([$_SESSION["id"], $idTutoria]);
        $tutoria = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tutoria) {
            Response::error("La tutoria ya no esta disponible", -1323, 404);
        }

        $stmt = $cn->prepare("SELECT h.id_horarios AS id_horario, h.dias_curso AS dia, h.hora_inicio, h.hora_fin,
            CASE WHEN h.estado = 'asignado' OR EXISTS (
                SELECT 1 FROM solicitudes s WHERE s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')
            ) THEN 'reservado' ELSE 'disponible' END AS disponibilidad,
            propia.id_solicitud, propia.estado AS estado_solicitud
            FROM horarios h LEFT JOIN solicitudes propia ON propia.id_horario = h.id_horarios AND propia.id_usuario = ?
            WHERE h.id_tutoria = ? AND h.estado NOT IN ('cancelado','finalizado')
            ORDER BY FIELD(h.dias_curso,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), h.hora_inicio");
        $stmt->execute([$_SESSION["id"], $idTutoria]);
        $tutoria["horarios"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Tutoria encontrada", 200, ["tutoria" => $tutoria]);
    } catch (PDOException $error) {
        Response::error("No se pudo cargar la tutoria", -1324, 500);
    }
?>