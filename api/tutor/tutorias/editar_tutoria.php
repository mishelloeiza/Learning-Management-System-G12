<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("POST");

    $idTutoria = trim($_POST["id_tutoria"] ?? '');
    if ($idTutoria === '' || !ctype_digit($idTutoria)) {
        Response::error("El id de la tutoria es obligatorio", -1160, 400);
    }

    $datos = TutorHelper::leerTutoria();
    $horarios = TutorHelper::leerHorarios();

    if ($datos["cupos"] > count($horarios)) {
        Response::error("Los cupos no pueden superar la cantidad de horarios", -1161, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $cn->beginTransaction();

        $stmt = $cn->prepare("SELECT estado, id_materia FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? FOR UPDATE");
        $stmt->execute([$idTutoria, $idTutor]);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            Response::error("No se encontro la tutoria", -1162, 404);
        }
        if (!in_array($actual["estado"], ["activa", "en curso"], true)) {
            Response::error("Solo se pueden modificar tutorias activas o en curso", -1163, 409);
        }

        $materiaCambio = (int) $actual["id_materia"] !== $datos["id_materia"];
        if ($materiaCambio && !TutorHelper::esEspecialista($cn, $idTutor, $datos["id_materia"])) {
            Response::error("Solo puede usar materias en las que es especialista", -1164, 403);
        }

        $stmt = $cn->prepare("SELECT h.id_horarios, h.dias_curso, h.hora_inicio,
            (SELECT COUNT(*) FROM solicitudes s WHERE s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')) AS activas,
            (SELECT COUNT(*) FROM solicitudes s WHERE s.id_horario = h.id_horarios) AS totales
            FROM horarios h WHERE h.id_tutoria = ? AND h.estado IN ('disponible','asignado') FOR UPDATE");
        $stmt->execute([$idTutoria]);
        $existentes = [];
        $conSolicitudes = 0;
        $aprobadas = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $existentes[(int) $fila["id_horarios"]] = $fila;
            $conSolicitudes += (int) $fila["activas"];
        }

        $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            WHERE h.id_tutoria = ? AND s.estado = 'aprobada'");
        $stmt->execute([$idTutoria]);
        $aprobadas = (int) $stmt->fetchColumn();

        if ($materiaCambio && $conSolicitudes > 0) {
            Response::error("No puede cambiar la materia mientras haya solicitudes pendientes o aprobadas", -1165, 409);
        }
        if ($datos["cupos"] < $aprobadas) {
            Response::error("Los cupos no pueden ser menores a los estudiantes ya aprobados ($aprobadas)", -1166, 409);
        }

        $idsRecibidos = [];
        foreach ($horarios as $h) {
            if ($h["id"] !== null) {
                if (!isset($existentes[$h["id"]])) {
                    Response::error("Horario no encontrado en esta tutoria", -1167, 404);
                }
                $idsRecibidos[] = $h["id"];
            }
            if (TutorHelper::hayChoque($cn, $idTutor, (int) $idTutoria, $h, $datos["fecha_inicio"], $datos["fecha_fin"])) {
                Response::error("Ya tiene otra tutoria el " . $h["dia"] . " a las " . substr($h["inicio"], 0, 5), -1168, 409);
            }
        }

        foreach ($existentes as $id => $fila) {
            if (in_array($id, $idsRecibidos, true)) {
                continue;
            }
            if ((int) $fila["activas"] > 0) {
                Response::error("No puede quitar el horario " . $fila["dias_curso"] . " " . substr($fila["hora_inicio"], 0, 5) . ": tiene solicitudes pendientes o aprobadas", -1169, 409);
            }
            if ((int) $fila["totales"] > 0) {
                $cn->prepare("UPDATE horarios SET estado = 'cancelado' WHERE id_horarios = ?")->execute([$id]);
            } else {
                $cn->prepare("DELETE FROM horarios WHERE id_horarios = ?")->execute([$id]);
            }
        }

        $upd = $cn->prepare("UPDATE horarios SET dias_curso = ?, hora_inicio = ?, hora_fin = ? WHERE id_horarios = ?");
        $ins = $cn->prepare("INSERT INTO horarios (hora_inicio, hora_fin, dias_curso, estado, id_tutoria) VALUES (?, ?, ?, 'disponible', ?)");
        foreach ($horarios as $h) {
            if ($h["id"] === null) {
                $ins->execute([$h["inicio"], $h["fin"], $h["dia"], $idTutoria]);
                continue;
            }
            $fila = $existentes[$h["id"]];
            $cambio = $fila["dias_curso"] !== $h["dia"] || $fila["hora_inicio"] !== $h["inicio"];
            if ($cambio && (int) $fila["activas"] > 0) {
                Response::error("No puede mover el horario " . $fila["dias_curso"] . " " . substr($fila["hora_inicio"], 0, 5) . ": tiene solicitudes pendientes o aprobadas", -1170, 409);
            }
            if ($cambio) {
                $upd->execute([$h["dia"], $h["inicio"], $h["fin"], $h["id"]]);
            }
        }

        $stmt = $cn->prepare("UPDATE tutorias SET titulo = ?, descripcion = ?, id_materia = ?, cupos = ?, fecha_inicio = ?, fecha_fin = ? WHERE id_tutoria = ?");
        $stmt->execute([$datos["titulo"], $datos["descripcion"], $datos["id_materia"], $datos["cupos"], $datos["fecha_inicio"], $datos["fecha_fin"], $idTutoria]);

        $cn->commit();
        Response::success("Tutoria modificada correctamente", 200);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo modificar la tutoria", -1171, 500);
    }
?>
