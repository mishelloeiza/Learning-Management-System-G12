<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

function validarSolicitud(PDO $cn, string $idHorario, string $idUsuario, ?int $excluir = null, bool $aprobar = false): void
{
    if (!ctype_digit($idHorario) || !ctype_digit($idUsuario)) {
        throw new DomainException('Horario o estudiante invalido');
    }
    $stmt = $cn->prepare("SELECT h.estado AS estado_horario, h.dias_curso, h.hora_inicio, h.hora_fin,
        t.id_tutoria, t.fecha_inicio, t.fecha_fin, t.estado, t.cupos
        FROM horarios h INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria
        WHERE h.id_horarios = ? FOR UPDATE");
    $stmt->execute([$idHorario]);
    $h = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$h || !in_array($h['estado'], ['activa','en curso'], true) || $h['fecha_fin'] < date('Y-m-d')) {
        throw new DomainException('La tutoria ya no esta disponible');
    }
    if ($h['estado_horario'] !== 'disponible') {
        throw new DomainException('El horario no esta disponible');
    }
    $stmt = $cn->prepare('SELECT id_rol, activo FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$idUsuario]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u || (int) $u['id_rol'] !== 1 || !(int) $u['activo']) {
        throw new DomainException('El usuario debe ser un estudiante activo');
    }
    $stmt = $cn->prepare("SELECT 1 FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario
        WHERE s.id_usuario = ? AND h.id_tutoria = ? AND s.id_solicitud <> ? LIMIT 1");
    $stmt->execute([$idUsuario, $h['id_tutoria'], $excluir ?? 0]);
    if ($stmt->fetchColumn()) {
        throw new DomainException('El estudiante ya tiene una solicitud para esta tutoria');
    }
    $stmt = $cn->prepare("SELECT 1 FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario
        INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria
        WHERE s.id_usuario = ? AND s.estado = 'aprobada' AND s.id_solicitud <> ?
        AND h.dias_curso = ? AND h.hora_inicio < ? AND h.hora_fin > ?
        AND t.fecha_inicio <= ? AND t.fecha_fin >= ? LIMIT 1");
    $stmt->execute([$idUsuario, $excluir ?? 0, $h['dias_curso'], $h['hora_fin'], $h['hora_inicio'], $h['fecha_fin'], $h['fecha_inicio']]);
    if ($stmt->fetchColumn()) {
        throw new DomainException('El estudiante ya tiene una tutoria aprobada en ese horario');
    }
    $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_horario = ? AND estado IN ('pendiente','aprobada') AND id_solicitud <> ?");
    $stmt->execute([$idHorario, $excluir ?? 0]);
    if ((int) $stmt->fetchColumn() > 0) {
        throw new DomainException('El horario ya tiene una solicitud activa');
    }
    $stmt = $cn->prepare("SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario
        WHERE h.id_tutoria = ? AND s.estado = 'aprobada' AND s.id_solicitud <> ?");
    $stmt->execute([$h['id_tutoria'], $excluir ?? 0]);
    if ((int) $stmt->fetchColumn() >= (int) $h['cupos']) {
        throw new DomainException('La tutoria ya alcanzo su cupo');
    }
}

    if($_SERVER["REQUEST_METHOD"] !== "PUT") {
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

    if (!in_array($Estado, ["pendiente", "aprobada", "rechazada"], true)) {
        Response::error("Estado invalido", -1005, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $cn->beginTransaction();
        $stmt = $cn->prepare("SELECT estado, id_horario, id_usuario FROM solicitudes WHERE id_solicitud = ? FOR UPDATE");
        $stmt->execute([$IdSolicitud]);
        $anterior = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$anterior) {
            $cn->rollBack();
            Response::error("No se encontro la solicitud", -1003, 404);
        }
        if ($anterior['estado'] !== 'pendiente' && ($Estado !== $anterior['estado'] ||
            $IdHorario != $anterior['id_horario'] || $IdUsuario != $anterior['id_usuario'])) {
            $cn->rollBack();
            Response::error("Una solicitud respondida no se puede modificar", -1006, 409);
        }
        if ($anterior['estado'] === 'pendiente' && $Estado !== 'rechazada') {
            validarSolicitud($cn, $IdHorario, $IdUsuario, (int) $IdSolicitud);
        }
        if ($Estado === 'rechazada' && ($IdHorario != $anterior['id_horario'] || $IdUsuario != $anterior['id_usuario'])) {
            $cn->rollBack();
            Response::error("No cambie el horario o estudiante al rechazar", -1007, 409);
        }
        if ($Estado === 'aprobada') {
            $FechaRespuesta = date('Y-m-d');
            $cn->prepare("UPDATE horarios SET estado = 'asignado' WHERE id_horarios = ?")->execute([$IdHorario]);
        } elseif ($Estado === 'rechazada' && $anterior['estado'] === 'pendiente') {
            $FechaRespuesta = date('Y-m-d');
        } elseif ($Estado === 'pendiente') {
            $FechaRespuesta = null;
        }
        $stmt = $cn->prepare("UPDATE solicitudes SET estado = ?, fecha_respuesta = ?, id_horario = ?, id_usuario = ? WHERE id_solicitud = ?");
        $stmt->execute([$Estado, $FechaRespuesta, $IdHorario, $IdUsuario, $IdSolicitud]);
        if ($Estado === 'aprobada') {
            $cn->prepare("UPDATE solicitudes SET estado = 'rechazada', fecha_respuesta = CURDATE()
                WHERE id_horario = ? AND estado = 'pendiente' AND id_solicitud <> ?")->execute([$IdHorario, $IdSolicitud]);
        }
        $cn->commit();

        Response::success("Solicitud modificada correctamente", 200);
    } catch (DomainException $error) {
        if (isset($cn) && $cn->inTransaction()) $cn->rollBack();
        Response::error($error->getMessage(), -1008, 409);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) $cn->rollBack();
        Response::error("No se pudo modificar la solicitud", -1004, 500);
    }
?>