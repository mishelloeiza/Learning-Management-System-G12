<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1350, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1351, 403);
    }
    $idSolicitud = trim($_POST["id_solicitud"] ?? "");
    if ($idSolicitud === "" || !ctype_digit($idSolicitud)) {
        Response::error("Solicitud invalida", -1352, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $cn->beginTransaction();
        $stmt = $cn->prepare("SELECT estado FROM solicitudes WHERE id_solicitud = ? AND id_usuario = ? FOR UPDATE");
        $stmt->execute([$idSolicitud, $_SESSION["id"]]);
        $estado = $stmt->fetchColumn();
        if ($estado !== "pendiente") {
            $cn->rollBack();
            Response::error("Solo puede eliminar solicitudes pendientes", -1353, 409);
        }

        $stmt = $cn->prepare("DELETE FROM solicitudes WHERE id_solicitud = ? AND id_usuario = ? AND estado = 'pendiente'");
        $stmt->execute([$idSolicitud, $_SESSION["id"]]);
        $cn->commit();
        Response::success("Solicitud eliminada correctamente", 200);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo eliminar la solicitud", -1354, 500);
    }
?>