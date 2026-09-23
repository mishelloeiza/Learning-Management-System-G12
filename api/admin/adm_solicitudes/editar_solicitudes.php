<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $IdSolicitud = trim($_POST["IdSolicitud"] ?? "");
    $Estado = trim($_POST["Estado"] ?? "");
    $FechaRespuesta = trim($_POST["FechaRespuesta"] ?? "");
    $IdHorario = trim($_POST["IdHorario"] ?? "");
    $IdUsuario = trim($_POST["IdUsuario"] ?? "");

    if(empty($IdSolicitud) || empty($Estado) || empty($IdHorario) || empty($IdUsuario)) {
        Response::error("Todos los campos son obligatorios", -1002, 400);
    }

    if(empty($FechaRespuesta)){
        $FechaRespuesta = null;
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("UPDATE solicitudes SET estado = ?, fecha_respuesta = ?, id_horario = ?, id_usuario = ? WHERE id_solicitud = ?");
        $stmt->execute([$Estado, $FechaRespuesta, $IdHorario, $IdUsuario, $IdSolicitud]);

        if($stmt->rowCount() === 0) {
            Response::error("No se encontro la solicitud", -1003, 404);
        }

        Response::success("Solicitud modificada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo modificar la solicitud", -1004, 500);
    }
?>