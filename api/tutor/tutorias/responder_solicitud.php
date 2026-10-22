<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2' || !isset($_SESSION['id'])) {
        Response::error("No autorizado", -1001, 403);
    }

    $idTutor = $_SESSION['id'];
    $idSolicitud = trim($_POST["id_solicitud"] ?? '');
    $accion = trim($_POST["accion"] ?? '');

    if($idSolicitud === '' || !ctype_digit($idSolicitud) || !in_array($accion, ['aprobar', 'rechazar'], true)) {
        Response::error("Datos invalidos", -1200, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("SELECT s.id_horario, h.id_tutoria FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria WHERE s.id_solicitud = ? AND t.id_tutor = ?");
        $stmt->execute([$idSolicitud, $idTutor]);
        $ref = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$ref) {
            Response::error("No se encontro la solicitud", -1201, 404);
        }

        $cn->beginTransaction();

        $stmt = $cn->prepare("SELECT estado, cupos FROM tutorias WHERE id_tutoria = ? FOR UPDATE");
        $stmt->execute([$ref["id_tutoria"]]);
        $tutoria = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $cn->prepare("SELECT estado FROM horarios WHERE id_horarios = ? FOR UPDATE");
        $stmt->execute([$ref["id_horario"]]);
        $estadoHorario = $stmt->fetchColumn();

        $stmt = $cn->prepare("SELECT estado FROM solicitudes WHERE id_solicitud = ? FOR UPDATE");
        $stmt->execute([$idSolicitud]);
        $estadoSolicitud = $stmt->fetchColumn();

        if($estadoSolicitud !== "pendiente") {
            $cn->rollBack();
            Response::error("La solicitud ya fue respondida (" . $estadoSolicitud . ")", -1202, 409);
        }

        $hoy = date("Y-m-d");

        if($accion === "rechazar") {
            $cn->prepare("UPDATE solicitudes SET estado = 'rechazada', fecha_respuesta = ? WHERE id_solicitud = ?")->execute([$hoy, $idSolicitud]);
            $cn->prepare("UPDATE horarios SET estado = 'disponible' WHERE id_horarios = ? AND estado = 'asignado'")->execute([$ref["id_horario"]]);
            $cn->commit();

            Response::success("Solicitud rechazada, el horario quedo disponible", 200);
        }

        if(!in_array($tutoria["estado"], ["activa", "en curso"], true)) {
            $cn->rollBack();
            Response::error("La tutoria ya no esta vigente", -1203, 409);
        }

        if($estadoHorario !== "disponible") {
            $cn->rollBack();
            Response::error("El horario ya no esta disponible", -1204, 409);
        }

        $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
            WHERE h.id_tutoria = ? AND s.estado = 'aprobada'");
        $stmt->execute([$ref["id_tutoria"]]);
        $aprobadas = (int) $stmt->fetchColumn();

        if($aprobadas >= (int) $tutoria["cupos"]) {
            $cn->rollBack();
            Response::error("La tutoria ya alcanzo su cupo maximo", -1205, 409);
        }

        $cn->prepare("UPDATE solicitudes SET estado = 'aprobada', fecha_respuesta = ? WHERE id_solicitud = ?")->execute([$hoy, $idSolicitud]);
        $cn->prepare("UPDATE horarios SET estado = 'asignado' WHERE id_horarios = ?")->execute([$ref["id_horario"]]);
        $cn->prepare("UPDATE solicitudes SET estado = 'rechazada', fecha_respuesta = ? WHERE id_horario = ? AND estado = 'pendiente' AND id_solicitud <> ?")
            ->execute([$hoy, $ref["id_horario"], $idSolicitud]);
        $cn->commit();

        $lleno = ($aprobadas + 1) >= (int) $tutoria["cupos"];

        Response::success($lleno ? "Solicitud aprobada. La tutoria alcanzo su cupo y se cerro a nuevas solicitudes" : "Solicitud aprobada", 200, ["cupo_lleno" => $lleno]);
    } catch (PDOException $error) {
        if(isset($cn) && $cn->inTransaction()) {
            $cn->rollBack();
        }
        Response::error("No se pudo responder la solicitud", -1206, 500);
    }
?>
