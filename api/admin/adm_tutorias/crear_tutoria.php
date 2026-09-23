<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $titulo = trim($_POST["titulo"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $fecha_inicio = trim($_POST["fecha_inicio"] ?? '');
    $fecha_fin = trim($_POST["fecha_fin"] ?? '');
    $id_tutor = trim($_POST["id_tutor"] ?? '');
    $id_materia = trim($_POST["id_materia"] ?? '');

    if(empty($titulo) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin) || empty($id_tutor) || empty($id_materia)) {
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    if(preg_match_all('/./su', $titulo) > 255 || preg_match_all('/./su', $descripcion) > 255) {
        Response::error("El titulo y la descripcion no pueden superar los 255 caracteres", -1003, 400);
    }

    if(!ctype_digit($id_tutor) || !ctype_digit($id_materia)) {
        Response::error("Datos invalidos", -1004, 400);
    }

    $inicio = DateTime::createFromFormat("Y-m-d", $fecha_inicio);
    $fin = DateTime::createFromFormat("Y-m-d", $fecha_fin);

    if(!$inicio || !$fin || $inicio->format("Y-m-d") !== $fecha_inicio || $fin->format("Y-m-d") !== $fecha_fin) {
        Response::error("Fechas invalidas", -1005, 400);
    }

    if($fin <= $inicio) {
        Response::error("La fecha de fin debe ser posterior a la fecha de inicio", -1006, 400);
    }

    try {
        $cn = new Connection("admin");

        $tutor = $cn->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? AND id_rol = 2");
        $tutor->execute([$id_tutor]);

        if(!$tutor->fetchColumn()) {
            Response::error("El usuario seleccionado no es un tutor", -1007, 400);
        }

        $stmt = $cn->prepare("INSERT INTO tutorias (titulo, descripcion, estado, fecha_inicio, fecha_fin, id_tutor, id_materia) VALUES (?, ?, 'activa', ?, ?, ?, ?)");
        $stmt->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $id_tutor, $id_materia]);

        Response::success("Tutoria creada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo crear la tutoria", -1008, 500);
    }
?>
