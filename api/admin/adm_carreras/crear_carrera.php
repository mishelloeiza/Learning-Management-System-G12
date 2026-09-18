<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3'){
        Response::error("No autorizado", -1001, 404);
    }

    $nombre = trim($_POST["nombre"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');

    if(empty($nombre) || empty($descripcion)){
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    try {
        $cn = new Connection("admin"); 

        $stmt = $cn->prepare("INSERT INTO carreras (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre, $descripcion]);

        Response::success("Carrera creada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo crear la carrera", -1003, 404);
    }
?>