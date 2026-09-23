<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $nombre = trim($_POST["nombre"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $id_carrera = trim($_POST["id_carrera"] ?? '');

    if(empty($nombre) || empty($descripcion) || empty($id_carrera)) {
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    if(preg_match_all('/./su', $nombre) > 255 || preg_match_all('/./su', $descripcion) > 255) {
        Response::error("El nombre y la descripcion no pueden superar los 255 caracteres", -1003, 400);
    }

    if(!ctype_digit($id_carrera)) {
        Response::error("Carrera invalida", -1004, 400);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("INSERT INTO materias (nombre, descripcion, id_carrera) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $id_carrera]);

        Response::success("Materia creada correctamente", 200);
    } catch (PDOException $error) {
        if((int)($error->errorInfo[1] ?? 0) === 1062) {
            Response::error("Ya existe una materia con ese nombre", -1005, 409);
        }
        Response::error("No se pudo crear la materia", -1006, 500);
    }
?>
