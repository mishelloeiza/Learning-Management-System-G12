<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $buscar = trim($_GET["buscar"] ?? '');
    $id_carrera = trim($_GET["id_carrera"] ?? '');

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("SELECT m.id_materia, m.nombre, m.descripcion, m.activo, m.id_carrera, c.nombre AS carrera
            FROM materias m INNER JOIN carreras c ON m.id_carrera = c.id_carrera
            WHERE m.nombre LIKE ? AND (? = '' OR m.id_carrera = ?) ORDER BY m.id_materia");
        $stmt->execute(["%" . $buscar . "%", $id_carrera, $id_carrera]);

        $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Materias encontradas", 200, ["materias" => $materias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las materias", -1002, 500);
    }
?>
