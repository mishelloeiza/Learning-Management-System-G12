<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("GET");

    $estado = trim($_GET["estado"] ?? '');
    $idMateria = trim($_GET["id_materia"] ?? '');
    $idTutoria = trim($_GET["id_tutoria"] ?? '');
    $desde = TutorHelper::leerFecha("fecha_inicio");
    $hasta = TutorHelper::leerFecha("fecha_fin");

    if ($estado !== '' && !in_array($estado, ['pendiente', 'aprobada', 'rechazada'], true)) {
        Response::error("Estado invalido", -1190, 400);
    }
    if (($idMateria !== '' && !ctype_digit($idMateria)) || ($idTutoria !== '' && !ctype_digit($idTutoria))) {
        Response::error("Filtro invalido", -1191, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("SELECT s.id_solicitud, s.estado, s.fecha_solicitud, s.fecha_respuesta,
            CONCAT(u.nombre, ' ', u.apellido) AS estudiante, u.correo,
            t.id_tutoria, t.titulo, t.cupos, t.estado AS estado_tutoria, m.id_materia, m.nombre AS materia,
            h.dias_curso AS dia, h.hora_inicio, h.hora_fin,
            (SELECT COUNT(*) FROM solicitudes sa INNER JOIN horarios ha ON sa.id_horario = ha.id_horarios
                WHERE ha.id_tutoria = t.id_tutoria AND sa.estado = 'aprobada') AS aprobadas
            FROM solicitudes s
            INNER JOIN horarios h ON s.id_horario = h.id_horarios
            INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
            INNER JOIN materias m ON t.id_materia = m.id_materia
            INNER JOIN estudiantes_info u ON s.id_usuario = u.id_usuario
            WHERE t.id_tutor = ? AND (? = '' OR s.estado = ?) AND (? = '' OR t.id_materia = ?) AND (? = '' OR t.id_tutoria = ?)
            AND (? = '' OR DATE(s.fecha_solicitud) >= ?) AND (? = '' OR DATE(s.fecha_solicitud) <= ?)
            ORDER BY FIELD(s.estado, 'pendiente', 'aprobada', 'rechazada'), s.fecha_solicitud");
        $stmt->execute([$idTutor, $estado, $estado, $idMateria, $idMateria, $idTutoria, $idTutoria, $desde, $desde, $hasta, $hasta]);

        Response::success("Solicitudes encontradas", 200, ["solicitudes" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las solicitudes", -1192, 500);
    }
?>
