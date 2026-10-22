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
