<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1330, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1331, 403);
    }

    $desde = trim($_GET["fecha_inicio"] ?? "");
    $hasta = trim($_GET["fecha_fin"] ?? "");
    $idTutor = trim($_GET["id_tutor"] ?? "");
    $idMateria = trim($_GET["id_materia"] ?? "");
    $offset = trim($_GET["offset"] ?? "0");
    foreach (["fecha_inicio" => $desde, "fecha_fin" => $hasta] as $fecha) {
        if ($fecha !== "" && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4)))) {
            Response::error("Fecha invalida", -1332, 400);
        }
    }
    if (($idTutor !== "" && !ctype_digit($idTutor)) || ($idMateria !== "" && !ctype_digit($idMateria)) || !ctype_digit($offset)) {
        Response::error("Filtro invalido", -1333, 400);
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
        $stmt = $cn->prepare("SELECT s.id_solicitud, s.estado, s.fecha_solicitud,
            t.id_tutoria, t.titulo, t.fecha_inicio, t.fecha_fin, m.nombre AS materia,
            CONCAT(u.nombre, ' ', u.apellido) AS tutor, h.dias_curso AS dia, h.hora_inicio, h.hora_fin
            FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
            INNER JOIN materias m ON t.id_materia = m.id_materia
            INNER JOIN usuarios_2 u ON t.id_tutor = u.id_usuario
            WHERE s.id_usuario = ?" . $condiciones . " ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC LIMIT 6 OFFSET " . (int) $offset);
        $stmt->execute($parametros);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hayMas = count($solicitudes) > 5;
        if ($hayMas) {
            array_pop($solicitudes);
        }

        Response::success("Solicitudes encontradas", 200, ["solicitudes" => $solicitudes, "hay_mas" => $hayMas]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las solicitudes", -1334, 500);
    }
?>