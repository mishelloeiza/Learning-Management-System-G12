<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $HoraInicio = trim($_POST["HoraInicio"] ?? '');
    $HoraFin = trim($_POST["HoraFin"] ?? '');
    $DiasCurso = trim($_POST["DiasCurso"] ?? '');
    $IdTutoria = trim($_POST["IdTutoria"] ?? '');

    if (empty($HoraInicio) || empty($HoraFin) || empty($DiasCurso) || empty($IdTutoria)) {
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    $Estado = "disponible";

    try {
        $cn = new Connection("admin");

        $stmt = $cn -> prepare("INSERT INTO horarios (hora_inicio, hora_fin, dias_curso, estado, id_tutoria) VALUES (?, ?, ?, ?, ?)");
        $stmt -> execute([$HoraInicio, $HoraFin, $DiasCurso, $Estado, $IdTutoria]);

        Response::success("Horario creado correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo crear el horario", -1003, 404);
    }
?>