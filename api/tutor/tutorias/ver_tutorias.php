<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2' || !isset($_SESSION['id'])) {
        Response::error("No autorizado", -1001, 403);
    }

    $idTutor = $_SESSION['id'];
    $estado = trim($_GET["estado"] ?? '');
    $idMateria = trim($_GET["id_materia"] ?? '');
    $desde = trim($_GET["fecha_inicio"] ?? '');
    $hasta = trim($_GET["fecha_fin"] ?? '');

    foreach([$desde, $hasta] as $fecha) {
        if($fecha !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4)))) {
            Response::error("Fecha invalida en el filtro", -1120, 400);
        }
    }

    if($estado !== '' && !in_array($estado, ['activa', 'en curso', 'finalizada', 'cancelada'], true)) {
        Response::error("Estado invalido", -1131, 400);
    }

    if($idMateria !== '' && !ctype_digit($idMateria)) {
        Response::error("Materia invalida", -1132, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $hoy = date("Y-m-d");

        $cn->prepare("UPDATE tutorias SET estado = 'en curso' WHERE id_tutor = ? AND estado = 'activa' AND ? BETWEEN fecha_inicio AND fecha_fin")
            ->execute([$idTutor, $hoy]);
        $cn->prepare("UPDATE tutorias SET estado = 'finalizada' WHERE id_tutor = ? AND estado IN ('activa','en curso') AND fecha_fin < ?")
            ->execute([$idTutor, $hoy]);
        $cn->prepare("UPDATE horarios h INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria SET h.estado = 'finalizado'
            WHERE t.id_tutor = ? AND t.estado = 'finalizada' AND h.estado IN ('disponible','asignado')")
            ->execute([$idTutor]);

        $stmt = $cn->prepare("SELECT t.id_tutoria, t.titulo, t.descripcion, t.estado, t.fecha_inicio, t.fecha_fin, t.cupos,
            t.id_materia, m.nombre AS materia,
            (SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
                WHERE h.id_tutoria = t.id_tutoria AND s.estado = 'aprobada') AS aprobadas,
            (SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
                WHERE h.id_tutoria = t.id_tutoria AND s.estado = 'pendiente') AS pendientes
            FROM tutorias t INNER JOIN materias m ON t.id_materia = m.id_materia
            WHERE t.id_tutor = ? AND (? = '' OR t.estado = ?) AND (? = '' OR t.id_materia = ?)
            AND (? = '' OR t.fecha_inicio >= ?) AND (? = '' OR t.fecha_fin <= ?)
            ORDER BY t.fecha_inicio, t.id_tutoria");
        $stmt->execute([$idTutor, $estado, $estado, $idMateria, $idMateria, $desde, $desde, $hasta, $hasta]);
        $tutorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $horarios = [];

        if(!empty($tutorias)) {
            $ids = array_column($tutorias, "id_tutoria");
            $marcas = implode(",", array_fill(0, count($ids), "?"));

            $stmt = $cn->prepare("SELECT h.id_horarios AS id_horario, h.id_tutoria, h.dias_curso AS dia, h.hora_inicio, h.hora_fin, h.estado,
                s.id_solicitud, s.estado AS estado_solicitud, CONCAT(u.nombre, ' ', u.apellido) AS estudiante
                FROM horarios h
                LEFT JOIN solicitudes s ON s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')
                LEFT JOIN estudiantes_info u ON s.id_usuario = u.id_usuario
                WHERE h.id_tutoria IN ($marcas) AND h.estado <> 'cancelado'
                ORDER BY FIELD(h.dias_curso,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), h.hora_inicio");
            $stmt->execute($ids);

            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $horarios[$fila["id_tutoria"]][] = $fila;
            }
        }

        foreach($tutorias as $i => $t) {
            $tutorias[$i]["horarios"] = $horarios[$t["id_tutoria"]] ?? [];
            $tutorias[$i]["abierta"] = in_array($t["estado"], ["activa", "en curso"], true) && (int) $t["aprobadas"] < (int) $t["cupos"];
        }

        Response::success("Tutorias encontradas", 200, ["tutorias" => $tutorias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las tutorias", -1133, 500);
    }
?>
