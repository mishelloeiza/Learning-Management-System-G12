<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1400, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "3") {
        Response::error("No autorizado", -1401, 403);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $stmt = $cn->query("SELECT
            (SELECT COUNT(*) FROM usuarios) AS usuarios,
            (SELECT COUNT(*) FROM usuarios WHERE id_rol = 1) AS estudiantes,
            (SELECT COUNT(*) FROM usuarios WHERE id_rol = 2) AS tutores,
            (SELECT COUNT(*) FROM carreras) AS carreras,
            (SELECT COUNT(*) FROM materias) AS materias,
            (SELECT COUNT(*) FROM tutorias) AS tutorias,
            (SELECT COUNT(*) FROM tutorias WHERE estado IN ('activa','en curso')) AS tutorias_vigentes,
            (SELECT COUNT(*) FROM horarios) AS horarios,
            (SELECT COUNT(*) FROM solicitudes) AS solicitudes,
            (SELECT COUNT(*) FROM solicitudes WHERE estado = 'pendiente') AS solicitudes_pendientes");

        Response::success("Estadisticas cargadas", 200, ["estadisticas" => $stmt->fetch(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las estadisticas", -1402, 500);
    }
?>
