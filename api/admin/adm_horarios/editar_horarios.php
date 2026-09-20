<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $IdHorarios = trim($_POST["IdHorarios"] ?? '');
    $HoraInicio = trim($_POST["HoraInicio"] ?? '');
    $HoraFin = trim($_POST["HoraFin"] ?? '');
    $DiasCurso = trim($_POST["DiasCurso"] ?? '');
    $Estado = trim($_POST["Estado" ?? '']);
    $IdTutoria = trim($_POST["IdTutoria"] ?? '');

    if (empty($IdHorarios) || empty($HoraInicio) || empty($HoraFin) || empty($DiasCurso) || empty($IdTutoria)) {
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn -> prepare("UPDATE horarios SET hora_inicio = ?, hora_fin = ?, dias_curso = ?, estado = ?, id_tutoria = ? WHERE id_horarios = ?");
        $stmt -> execute([$HoraInicio, $HoraFin, $DiasCurso, $Estado, $IdTutoria, $IdHorarios]);

        if ($stmt -> rowCount() === 0) {
            Response::error("No se encontro el horario", -1003, 404);
        }

        Response::success("Horario editado correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo modificar el horario", -1004, 500);
    }
?>