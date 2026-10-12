<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("GET");

    $estado = trim($_GET["estado"] ?? '');
    $idMateria = trim($_GET["id_materia"] ?? '');
    $desde = TutorHelper::leerFecha("fecha_inicio");
    $hasta = TutorHelper::leerFecha("fecha_fin");

    if ($estado !== '' && !in_array($estado, ['activa', 'en curso', 'finalizada', 'cancelada'], true)) {
        Response::error("Estado invalido", -1131, 400);
    }
    if ($idMateria !== '' && !ctype_digit($idMateria)) {
        Response::error("Materia invalida", -1132, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        TutorHelper::sincronizarEstados($cn, $idTutor);

        $tutorias = TutorHelper::consultarTutorias($cn, $idTutor, $estado, $idMateria, $desde, $hasta);

        Response::success("Tutorias encontradas", 200, ["tutorias" => $tutorias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las tutorias", -1133, 500);
    }
?>
