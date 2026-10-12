<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("GET");

    $idMateria = trim($_GET["id_materia"] ?? '');
    $desde = TutorHelper::leerFecha("fecha_inicio");
    $hasta = TutorHelper::leerFecha("fecha_fin");

    if ($idMateria !== '' && !ctype_digit($idMateria)) {
        Response::error("Materia invalida", -1140, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        TutorHelper::sincronizarEstados($cn, $idTutor);

        $tutorias = TutorHelper::consultarTutorias($cn, $idTutor, "en curso", $idMateria, $desde, $hasta);

        Response::success("Tutorias en curso encontradas", 200, ["tutorias" => $tutorias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las tutorias en curso", -1141, 500);
    }
?>
