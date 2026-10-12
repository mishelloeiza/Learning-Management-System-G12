<?php
    require_once(__DIR__ . "/../../../config/TutorHelper.php");

    $idTutor = TutorHelper::iniciar("GET");

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("SELECT m.id_materia, m.nombre FROM materias m INNER JOIN usuarios_2 u ON m.id_carrera = u.id_carrera
            WHERE u.id_usuario = ? AND m.activo = true ORDER BY m.nombre");
        $stmt->execute([$idTutor]);

        Response::success("Materias encontradas", 200, ["materias" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las materias", -1130, 500);
    }
?>
