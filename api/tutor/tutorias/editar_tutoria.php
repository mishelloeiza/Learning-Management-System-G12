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
    $titulo = trim($_POST["titulo"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $idMateria = trim($_POST["id_materia"] ?? '');
    $cupos = trim($_POST["cupos"] ?? '');
    $fechaInicio = trim($_POST["fecha_inicio"] ?? '');
    $fechaFin = trim($_POST["fecha_fin"] ?? '');
    $horariosRecibidos = json_decode($_POST["horarios"] ?? '', true);

    if($idTutoria === '' || !ctype_digit($idTutoria)) {
        Response::error("El id de la tutoria es obligatorio", -1160, 400);
    }

    if($titulo === '' || $descripcion === '' || $idMateria === '' || $cupos === '' || $fechaInicio === '' || $fechaFin === '') {
        Response::error("Todos los campos son obligatorios", -1100, 400);
    }

    if(preg_match_all('/./su', $titulo) > 255 || preg_match_all('/./su', $descripcion) > 255) {
        Response::error("El titulo y la descripcion no pueden superar los 255 caracteres", -1101, 400);
    }

    if(!ctype_digit($idMateria) || !ctype_digit($cupos) || (int) $cupos < 1 || (int) $cupos > 5) {
        Response::error("Materia o cupos invalidos (los cupos van de 1 a 5)", -1102, 400);
    }

    $inicio = DateTime::createFromFormat("Y-m-d", $fechaInicio);
    $fin = DateTime::createFromFormat("Y-m-d", $fechaFin);

    if(!$inicio || !$fin || $inicio->format("Y-m-d") !== $fechaInicio || $fin->format("Y-m-d") !== $fechaFin) {
        Response::error("Fechas invalidas", -1103, 400);
    }

    if($fin <= $inicio) {
        Response::error("La fecha de fin debe ser posterior a la fecha de inicio", -1104, 400);
    }

    if(!is_array($horariosRecibidos) || count($horariosRecibidos) < 1 || count($horariosRecibidos) > 5) {
        Response::error("Debe registrar entre 1 y 5 horarios", -1110, 400);
    }

    $horarios = [];

    foreach($horariosRecibidos as $h) {
        $dia = is_array($h) ? trim((string) ($h["dia"] ?? '')) : '';
        $horaInicio = is_array($h) ? trim((string) ($h["hora_inicio"] ?? '')) : '';
        $idHorario = is_array($h) ? trim((string) ($h["id_horario"] ?? '')) : '';

        if(!in_array($dia, ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'], true)) {
            Response::error("Dia de horario invalido", -1111, 400);
        }

        if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $horaInicio)) {
            Response::error("Hora de inicio invalida (formato HH:MM)", -1112, 400);
        }

        if($horaInicio >= "23:00") {
            Response::error("Cada horario dura 1 hora: debe iniciar antes de las 23:00", -1113, 400);
        }

        if($idHorario !== '' && !ctype_digit($idHorario)) {
            Response::error("Horario invalido", -1114, 400);
        }

        $horaFin = (new DateTime("2000-01-01 " . $horaInicio))->modify("+1 hour")->format("H:i:s");
        $horarios[] = ["id" => $idHorario === '' ? null : (int) $idHorario, "dia" => $dia, "inicio" => $horaInicio . ":00", "fin" => $horaFin];
    }

    for($i = 0; $i < count($horarios); $i++) {
        for($j = $i + 1; $j < count($horarios); $j++) {
            if($horarios[$i]["dia"] === $horarios[$j]["dia"] && $horarios[$i]["inicio"] < $horarios[$j]["fin"] && $horarios[$j]["inicio"] < $horarios[$i]["fin"]) {
                Response::error("Hay horarios que se traslapan el mismo dia", -1115, 400);
            }
        }
    }

    if((int) $cupos > count($horarios)) {
        Response::error("Los cupos no pueden superar la cantidad de horarios", -1161, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $cn->beginTransaction();

        $stmt = $cn->prepare("SELECT estado, id_materia FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? FOR UPDATE");
        $stmt->execute([$idTutoria, $idTutor]);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$actual) {
            $cn->rollBack();
            Response::error("No se encontro la tutoria", -1162, 404);
        }

        if(!in_array($actual["estado"], ["activa", "en curso"], true)) {
            $cn->rollBack();
            Response::error("Solo se pueden modificar tutorias activas o en curso", -1163, 409);
        }

        $materiaCambio = (int) $actual["id_materia"] !== (int) $idMateria;

        if($materiaCambio) {
            $especialista = $cn->prepare("SELECT 1 FROM materias m INNER JOIN usuarios_2 u ON m.id_carrera = u.id_carrera
                WHERE u.id_usuario = ? AND m.id_materia = ? AND m.activo = true");
            $especialista->execute([$idTutor, $idMateria]);

            if(!$especialista->fetchColumn()) {
                $cn->rollBack();
                Response::error("Solo puede usar materias en las que es especialista", -1164, 403);
            }
        }

        $stmt = $cn->prepare("SELECT h.id_horarios, h.dias_curso, h.hora_inicio,
            (SELECT COUNT(*) FROM solicitudes s WHERE s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')) AS activas,
            (SELECT COUNT(*) FROM solicitudes s WHERE s.id_horario = h.id_horarios) AS totales
            FROM horarios h WHERE h.id_tutoria = ? AND h.estado IN ('disponible','asignado') FOR UPDATE");
        $stmt->execute([$idTutoria]);

        $existentes = [];
        $conSolicitudes = 0;

        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $existentes[(int) $fila["id_horarios"]] = $fila;
            $conSolicitudes += (int) $fila["activas"];
        }

        $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            WHERE h.id_tutoria = ? AND s.estado = 'aprobada'");
        $stmt->execute([$idTutoria]);
        $aprobadas = (int) $stmt->fetchColumn();

        if($materiaCambio && $conSolicitudes > 0) {
            $cn->rollBack();
            Response::error("No puede cambiar la materia mientras haya solicitudes pendientes o aprobadas", -1165, 409);
        }

        if((int) $cupos < $aprobadas) {
            $cn->rollBack();
            Response::error("Los cupos no pueden ser menores a los estudiantes ya aprobados ($aprobadas)", -1166, 409);
        }

        $choque = $cn->prepare("SELECT 1 FROM horarios hr INNER JOIN tutorias t ON hr.id_tutoria = t.id_tutoria
            WHERE t.id_tutor = ? AND t.id_tutoria <> ? AND t.estado IN ('activa','en curso') AND hr.estado IN ('disponible','asignado')
            AND hr.dias_curso = ? AND hr.hora_inicio < ? AND hr.hora_fin > ?
            AND t.fecha_inicio <= ? AND t.fecha_fin >= ? LIMIT 1");

        $idsRecibidos = [];

        foreach($horarios as $h) {
            if($h["id"] !== null) {
                if(!isset($existentes[$h["id"]])) {
                    $cn->rollBack();
                    Response::error("Horario no encontrado en esta tutoria", -1167, 404);
                }
                $idsRecibidos[] = $h["id"];
            }

            $choque->execute([$idTutor, $idTutoria, $h["dia"], $h["fin"], $h["inicio"], $fechaFin, $fechaInicio]);

            if($choque->fetchColumn()) {
                $cn->rollBack();
                Response::error("Ya tiene otra tutoria el " . $h["dia"] . " a las " . substr($h["inicio"], 0, 5), -1168, 409);
            }
        }

        foreach($existentes as $id => $fila) {
            if(in_array($id, $idsRecibidos, true)) {
                continue;
            }

            if((int) $fila["activas"] > 0) {
                $cn->rollBack();
                Response::error("No puede quitar el horario " . $fila["dias_curso"] . " " . substr($fila["hora_inicio"], 0, 5) . ": tiene solicitudes pendientes o aprobadas", -1169, 409);
            }

            if((int) $fila["totales"] > 0) {
                $cn->prepare("UPDATE horarios SET estado = 'cancelado' WHERE id_horarios = ?")->execute([$id]);
            } else {
                $cn->prepare("DELETE FROM horarios WHERE id_horarios = ?")->execute([$id]);
            }
        }

        $actualizar = $cn->prepare("UPDATE horarios SET dias_curso = ?, hora_inicio = ?, hora_fin = ? WHERE id_horarios = ?");
        $insertar = $cn->prepare("INSERT INTO horarios (hora_inicio, hora_fin, dias_curso, estado, id_tutoria) VALUES (?, ?, ?, 'disponible', ?)");

        foreach($horarios as $h) {
            if($h["id"] === null) {
                $insertar->execute([$h["inicio"], $h["fin"], $h["dia"], $idTutoria]);
                continue;
            }

            $fila = $existentes[$h["id"]];
            $cambio = $fila["dias_curso"] !== $h["dia"] || $fila["hora_inicio"] !== $h["inicio"];

            if($cambio && (int) $fila["activas"] > 0) {
                $cn->rollBack();
                Response::error("No puede mover el horario " . $fila["dias_curso"] . " " . substr($fila["hora_inicio"], 0, 5) . ": tiene solicitudes pendientes o aprobadas", -1170, 409);
            }

            if($cambio) {
                $actualizar->execute([$h["dia"], $h["inicio"], $h["fin"], $h["id"]]);
            }
        }

        $stmt = $cn->prepare("UPDATE tutorias SET titulo = ?, descripcion = ?, id_materia = ?, cupos = ?, fecha_inicio = ?, fecha_fin = ? WHERE id_tutoria = ?");
        $stmt->execute([$titulo, $descripcion, $idMateria, $cupos, $fechaInicio, $fechaFin, $idTutoria]);

        $cn->commit();

        Response::success("Tutoria modificada correctamente", 200);
    } catch (PDOException $error) {
        if(isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo modificar la tutoria", -1171, 500);
    }
?>
