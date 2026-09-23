<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3'){
        Response::error("No autorizado", -1001, 404);
    }

    $IdHorario = trim($_POST["IdHorario"] ?? '');
    $IdUsuario = trim($_POST["IdUsuario"] ?? '');

    if(empty($IdHorario) || empty($IdUsuario)){
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    $Estado = "pendiente";

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("INSERT INTO solicitudes (estado, id_horario, id_usuario) VALUES (?, ?, ?)");
        $stmt->execute([$Estado, $IdHorario, $IdUsuario]);

        Response::success("Solicitud creada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo crear la solicitud", -1003, 404);
    }
?>