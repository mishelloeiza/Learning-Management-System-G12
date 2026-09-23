<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("SELECT id_materia, nombre, activo FROM materias ORDER BY nombre");
        $stmt->execute();
        $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($materias)) {
            Response::error("No se encontraron materias", -1002, 404);
        }

        Response::success("Materias encontradas", 200, ["materias" => $materias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las materias", -1003, 500);
    }
?>
