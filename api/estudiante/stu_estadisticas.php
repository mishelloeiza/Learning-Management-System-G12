<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1410, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1411, 403);
    }

    $idEstudiante = $_SESSION["id"];

    try {
        $cn = new Connection($_SESSION["rol"]);
        // "Disponibles" usa el mismo criterio que la lista de tutorias del dashboard
        $stmt = $cn->prepare("SELECT
            (SELECT COUNT(*) FROM tutorias WHERE estado IN ('activa','en curso') AND fecha_fin >= CURDATE()) AS disponibles,
            (SELECT COUNT(*) FROM solicitudes WHERE id_usuario = ?) AS total,
            (SELECT COUNT(*) FROM solicitudes WHERE id_usuario = ? AND estado = 'pendiente') AS pendientes,
            (SELECT COUNT(*) FROM solicitudes WHERE id_usuario = ? AND estado = 'aprobada') AS aprobadas,
            (SELECT COUNT(*) FROM solicitudes WHERE id_usuario = ? AND estado = 'rechazada') AS rechazadas,
            (SELECT COUNT(DISTINCT h.id_tutoria) FROM solicitudes s
                INNER JOIN horarios h ON s.id_horario = h.id_horarios
                INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
                WHERE s.id_usuario = ? AND s.estado = 'aprobada'
                AND t.estado IN ('activa','en curso') AND t.fecha_fin >= CURDATE()) AS tutorias_vigentes");
        $stmt->execute([$idEstudiante, $idEstudiante, $idEstudiante, $idEstudiante, $idEstudiante]);

        Response::success("Estadisticas cargadas", 200, ["estadisticas" => $stmt->fetch(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las estadisticas", -1412, 500);
    }
?>
