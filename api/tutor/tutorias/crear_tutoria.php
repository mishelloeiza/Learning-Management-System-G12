<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("POST");

    $datos = TutorHelper::leerTutoria();
    $horarios = TutorHelper::leerHorarios();

    if ($datos["fecha_inicio"] < date("Y-m-d")) {
        Response::error("La fecha de inicio no puede ser anterior a hoy", -1150, 400);
    }
    if ($datos["cupos"] > count($horarios)) {
        Response::error("Los cupos no pueden superar la cantidad de horarios", -1151, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        if (!TutorHelper::esEspecialista($cn, $idTutor, $datos["id_materia"])) {
            Response::error("Solo puede crear tutorias de las materias en las que es especialista", -1152, 403);
        }

        foreach ($horarios as $h) {
            if (TutorHelper::hayChoque($cn, $idTutor, null, $h, $datos["fecha_inicio"], $datos["fecha_fin"])) {
                Response::error("Ya tiene otra tutoria el " . $h["dia"] . " a las " . substr($h["inicio"], 0, 5), -1153, 409);
            }
        }

        $cn->beginTransaction();

        $stmt = $cn->prepare("INSERT INTO tutorias (titulo, descripcion, estado, fecha_inicio, fecha_fin, cupos, id_tutor, id_materia) VALUES (?, ?, 'activa', ?, ?, ?, ?, ?)");
        $stmt->execute([$datos["titulo"], $datos["descripcion"], $datos["fecha_inicio"], $datos["fecha_fin"], $datos["cupos"], $idTutor, $datos["id_materia"]]);
        $idTutoria = $cn->lastInsertId();

        $ins = $cn->prepare("INSERT INTO horarios (hora_inicio, hora_fin, dias_curso, estado, id_tutoria) VALUES (?, ?, ?, 'disponible', ?)");
        foreach ($horarios as $h) {
            $ins->execute([$h["inicio"], $h["fin"], $h["dia"], $idTutoria]);
        }

        $cn->commit();
        Response::success("Tutoria creada correctamente", 200, ["id_tutoria" => (int) $idTutoria]);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo crear la tutoria", -1154, 500);
    }
?>
