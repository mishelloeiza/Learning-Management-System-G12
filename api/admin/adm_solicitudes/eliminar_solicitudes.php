<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $IdSolicitud = trim($_POST["IdSolicitud"] ?? '');

    if(empty($IdSolicitud)) {
        Response::error("El id de la solicitud es obligatorio", -1002, 400);
    }

    try {
        $cn = new Connection("admin");

        // La tabla solicitudes no tiene columna "activo", el borrado
        // logico se hace pasando el estado a 'rechazada' y guardando
        // la fecha de hoy como fecha de respuesta
        $stmt = $cn->prepare("UPDATE solicitudes SET estado = 'rechazada', fecha_respuesta = CURDATE() WHERE id_solicitud = ?");
        $stmt->execute([$IdSolicitud]);

        if($stmt->rowCount() === 0) {
            Response::error("No se encontro la solicitud", -1003, 404);
        }

        Response::success("Solicitud rechazada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo rechazar la solicitud", -1004, 500);
    }
?>