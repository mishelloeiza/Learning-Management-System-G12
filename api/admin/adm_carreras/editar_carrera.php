<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

    if($_SERVER["REQUEST_METHOD"] !== "PUT") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $id_carrera = trim($_POST["id_carrera"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $activo = trim($_POST["activo"] ?? 0);

    if(empty($id_carrera) || empty($nombre) || empty($descripcion)) {
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("UPDATE carreras SET nombre = ?, descripcion = ?, activo = ? WHERE id_carrera = ?");
        $stmt->execute([$nombre, $descripcion, $activo, $id_carrera]);

        if($stmt->rowCount() === 0) {
            Response::error("No se encontro la carrera", -1003, 404);
        }

        Response::success("Carrera modificada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo modificar la carrera", -1004, 500);
    }
?>