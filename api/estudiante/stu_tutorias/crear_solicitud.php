<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1340, 405);
    }
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "1" || !isset($_SESSION["id"])) {
        Response::error("No autorizado", -1341, 403);
    }
    $idHorario = trim($_POST["id_horario"] ?? "");
    if ($idHorario === "" || !ctype_digit($idHorario)) {
        Response::error("Horario invalido", -1342, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);
        $stmt = $cn->prepare("SELECT id_tutoria FROM horarios WHERE id_horarios = ?");
        $stmt->execute([$idHorario]);
        $idTutoria = $stmt->fetchColumn();
        if (!$idTutoria) {
            Response::error("No se encontro el horario", -1343, 404);
        }

        $cn->beginTransaction();
        $stmt = $cn->prepare("SELECT estado, fecha_fin FROM tutorias WHERE id_tutoria = ? FOR UPDATE");
        $stmt->execute([$idTutoria]);
        $tutoria = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tutoria || !in_array($tutoria["estado"], ["activa", "en curso"], true) || $tutoria["fecha_fin"] < date("Y-m-d")) {
            $cn->rollBack();
            Response::error("La tutoria ya no esta disponible", -1344, 409);
        }

        $stmt = $cn->prepare("SELECT estado FROM horarios WHERE id_horarios = ? AND id_tutoria = ? FOR UPDATE");
        $stmt->execute([$idHorario, $idTutoria]);
        $estadoHorario = $stmt->fetchColumn();
        if ($estadoHorario !== "disponible") {
            $cn->rollBack();
            Response::error("El horario ya esta reservado", -1345, 409);
        }

        $stmt = $cn->prepare("SELECT s.estado FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            WHERE h.id_tutoria = ? AND s.id_usuario = ? AND s.estado IN ('pendiente','aprobada','rechazada')
            ORDER BY FIELD(s.estado, 'rechazada','aprobada','pendiente') LIMIT 1");
        $stmt->execute([$idTutoria, $_SESSION["id"]]);
        $solicitudAnterior = $stmt->fetchColumn();
        if ($solicitudAnterior === "rechazada") {
            $cn->rollBack();
            Response::error("No puede volver a solicitar una tutoria que fue rechazada", -1346, 409);
        }
        if ($solicitudAnterior !== false) {
            $cn->rollBack();
            Response::error("Ya tiene una solicitud para esta tutoria", -1347, 409);
        }

        $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_horario = ? AND estado IN ('pendiente','aprobada')");
        $stmt->execute([$idHorario]);
        if ((int) $stmt->fetchColumn() > 0) {
            $cn->rollBack();
            Response::error("El horario ya esta reservado", -1345, 409);
        }

        $stmt = $cn->prepare("INSERT INTO solicitudes (estado, id_horario, id_usuario) VALUES ('pendiente', ?, ?)");
        $stmt->execute([$idHorario, $_SESSION["id"]]);
        $cn->commit();

        Response::success("Solicitud creada correctamente", 200, ["id_solicitud" => (int) $cn->lastInsertId()]);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo crear la solicitud", -1348, 500);
    }
?>