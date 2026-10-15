<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1300, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1301, 403);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $tutores = $cn->query("SELECT DISTINCT u.id_usuario AS id_tutor, CONCAT(u.nombre, ' ', u.apellido) AS tutor
            FROM tutorias t INNER JOIN usuarios_2 u ON t.id_tutor = u.id_usuario
            WHERE t.estado IN ('activa','en curso') ORDER BY tutor")->fetchAll(PDO::FETCH_ASSOC);
        $materias = $cn->query("SELECT DISTINCT m.id_materia, m.nombre AS materia
            FROM tutorias t INNER JOIN materias m ON t.id_materia = m.id_materia
            WHERE t.estado IN ('activa','en curso') ORDER BY materia")->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Filtros encontrados", 200, ["tutores" => $tutores, "materias" => $materias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar los filtros", -1302, 500);
    }
?>