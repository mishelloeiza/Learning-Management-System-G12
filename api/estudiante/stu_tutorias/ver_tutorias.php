<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1310, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1311, 403);
    }

    $desde = trim($_GET["fecha_inicio"] ?? "");
    $hasta = trim($_GET["fecha_fin"] ?? "");
    $idTutor = trim($_GET["id_tutor"] ?? "");
    $idMateria = trim($_GET["id_materia"] ?? "");
    $offset = trim($_GET["offset"] ?? "0");
    foreach (["fecha_inicio" => $desde, "fecha_fin" => $hasta] as $nombre => $fecha) {
        if ($fecha !== "" && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4)))) {
            Response::error("Fecha invalida", -1312, 400);
        }
    }
    if (($idTutor !== "" && !ctype_digit($idTutor)) || ($idMateria !== "" && !ctype_digit($idMateria)) || !ctype_digit($offset)) {
        Response::error("Filtro invalido", -1313, 400);
    }

    $filtros = [];
    $parametros = [$_SESSION["id"]];
    if ($desde !== "") {
        $filtros[] = "t.fecha_fin >= ?";
        $parametros[] = $desde;
    }
    if ($hasta !== "") {
        $filtros[] = "t.fecha_inicio <= ?";
        $parametros[] = $hasta;
    }
    if ($idTutor !== "") {
        $filtros[] = "t.id_tutor = ?";
        $parametros[] = $idTutor;
    }
    if ($idMateria !== "") {
        $filtros[] = "t.id_materia = ?";
        $parametros[] = $idMateria;
    }
    $condiciones = empty($filtros) ? "" : " AND " . implode(" AND ", $filtros);

    try {
        $cn = new Connection($_SESSION["rol"]);
        $stmt = $cn->prepare("SELECT t.id_tutoria, t.titulo, t.fecha_inicio, t.fecha_fin,
            CONCAT(u.nombre, ' ', u.apellido) AS tutor, m.nombre AS materia,
            (SELECT s.estado FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
                WHERE h.id_tutoria = t.id_tutoria AND s.id_usuario = ?
                ORDER BY FIELD(s.estado, 'rechazada', 'aprobada', 'pendiente') LIMIT 1) AS estado_solicitud
            FROM tutorias t INNER JOIN usuarios_2 u ON t.id_tutor = u.id_usuario
            INNER JOIN materias m ON t.id_materia = m.id_materia
            WHERE t.estado IN ('activa','en curso') AND t.fecha_fin >= CURDATE()
            " . $condiciones . " ORDER BY t.fecha_inicio, t.id_tutoria LIMIT 6 OFFSET " . (int) $offset);
        $stmt->execute($parametros);
        $tutorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hayMas = count($tutorias) > 5;
        if ($hayMas) {
            array_pop($tutorias);
        }

        Response::success("Tutorias encontradas", 200, ["tutorias" => $tutorias, "hay_mas" => $hayMas]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las tutorias", -1314, 500);
    }
?>