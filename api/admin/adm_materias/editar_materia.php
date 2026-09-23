<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $id_materia = trim($_POST["id_materia"] ?? '');
    $nombre = trim($_POST["nombre"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $id_carrera = trim($_POST["id_carrera"] ?? '');
    $activo = trim($_POST["activo"] ?? '0');

    if(empty($id_materia) || empty($nombre) || empty($descripcion) || empty($id_carrera)) {
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    if(preg_match_all('/./su', $nombre) > 255 || preg_match_all('/./su', $descripcion) > 255) {
        Response::error("El nombre y la descripcion no pueden superar los 255 caracteres", -1003, 400);
    }

    if(!ctype_digit($id_materia) || !ctype_digit($id_carrera) || !in_array($activo, ['0', '1'], true)) {
        Response::error("Datos invalidos", -1004, 400);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("UPDATE materias SET nombre = ?, descripcion = ?, id_carrera = ?, activo = ? WHERE id_materia = ?");
        $stmt->execute([$nombre, $descripcion, $id_carrera, $activo, $id_materia]);

        if($stmt->rowCount() === 0) {
            $existe = $cn->prepare("SELECT 1 FROM materias WHERE id_materia = ?");
            $existe->execute([$id_materia]);

            if(!$existe->fetchColumn()) {
                Response::error("No se encontro la materia", -1005, 404);
            }
        }

        Response::success("Materia modificada correctamente", 200);
    } catch (PDOException $error) {
        if((int)($error->errorInfo[1] ?? 0) === 1062) {
            Response::error("Ya existe una materia con ese nombre", -1006, 409);
        }
        Response::error("No se pudo modificar la materia", -1007, 500);
    }
?>
