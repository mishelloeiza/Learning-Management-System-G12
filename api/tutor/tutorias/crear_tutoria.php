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
    $titulo = trim($_POST["titulo"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $idMateria = trim($_POST["id_materia"] ?? '');
    $cupos = trim($_POST["cupos"] ?? '');
    $fechaInicio = trim($_POST["fecha_inicio"] ?? '');
    $fechaFin = trim($_POST["fecha_fin"] ?? '');
    $horariosRecibidos = json_decode($_POST["horarios"] ?? '', true);

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

        if(!in_array($dia, ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'], true)) {
            Response::error("Dia de horario invalido", -1111, 400);
        }

        if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $horaInicio)) {
            Response::error("Hora de inicio invalida (formato HH:MM)", -1112, 400);
        }

        if($horaInicio >= "23:00") {
            Response::error("Cada horario dura 1 hora: debe iniciar antes de las 23:00", -1113, 400);
        }

        $horaFin = (new DateTime("2000-01-01 " . $horaInicio))->modify("+1 hour")->format("H:i:s");
        $horarios[] = ["dia" => $dia, "inicio" => $horaInicio . ":00", "fin" => $horaFin];
    }

    for($i = 0; $i < count($horarios); $i++) {
        for($j = $i + 1; $j < count($horarios); $j++) {
            if($horarios[$i]["dia"] === $horarios[$j]["dia"] && $horarios[$i]["inicio"] < $horarios[$j]["fin"] && $horarios[$j]["inicio"] < $horarios[$i]["fin"]) {
                Response::error("Hay horarios que se traslapan el mismo dia", -1115, 400);
            }
        }
    }

    if($fechaInicio < date("Y-m-d")) {
        Response::error("La fecha de inicio no puede ser anterior a hoy", -1150, 400);
    }

    if((int) $cupos > count($horarios)) {
        Response::error("Los cupos no pueden superar la cantidad de horarios", -1151, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $especialista = $cn->prepare("SELECT 1 FROM materias m INNER JOIN usuarios_2 u ON m.id_carrera = u.id_carrera
            WHERE u.id_usuario = ? AND m.id_materia = ? AND m.activo = true");
        $especialista->execute([$idTutor, $idMateria]);

        if(!$especialista->fetchColumn()) {
            Response::error("Solo puede crear tutorias de las materias en las que es especialista", -1152, 403);
        }

        $choque = $cn->prepare("SELECT 1 FROM horarios hr INNER JOIN tutorias t ON hr.id_tutoria = t.id_tutoria
            WHERE t.id_tutor = ? AND t.estado IN ('activa','en curso') AND hr.estado IN ('disponible','asignado')
            AND hr.dias_curso = ? AND hr.hora_inicio < ? AND hr.hora_fin > ?
            AND t.fecha_inicio <= ? AND t.fecha_fin >= ? LIMIT 1");

        foreach($horarios as $h) {
            $choque->execute([$idTutor, $h["dia"], $h["fin"], $h["inicio"], $fechaFin, $fechaInicio]);

            if($choque->fetchColumn()) {
                Response::error("Ya tiene otra tutoria el " . $h["dia"] . " a las " . substr($h["inicio"], 0, 5), -1153, 409);
            }
        }

        $cn->beginTransaction();

        $stmt = $cn->prepare("INSERT INTO tutorias (titulo, descripcion, estado, fecha_inicio, fecha_fin, cupos, id_tutor, id_materia) VALUES (?, ?, 'activa', ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $descripcion, $fechaInicio, $fechaFin, $cupos, $idTutor, $idMateria]);
        $idTutoria = $cn->lastInsertId();

        $stmt = $cn->prepare("INSERT INTO horarios (hora_inicio, hora_fin, dias_curso, estado, id_tutoria) VALUES (?, ?, ?, 'disponible', ?)");
        foreach($horarios as $h) {
            $stmt->execute([$h["inicio"], $h["fin"], $h["dia"], $idTutoria]);
        }

        $cn->commit();

        Response::success("Tutoria creada correctamente", 200, ["id_tutoria" => (int) $idTutoria]);
    } catch (PDOException $error) {
        if(isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo crear la tutoria", -1154, 500);
    }
?>
