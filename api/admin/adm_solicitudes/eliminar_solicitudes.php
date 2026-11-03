<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

    if($_SERVER["REQUEST_METHOD"] !== "DELETE") {
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
        $cn = new Connection($_SESSION["rol"]);

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