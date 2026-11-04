<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

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
        $cn = new Connection($_SESSION["rol"]);

        $cn->beginTransaction();
        validarSolicitud($cn, $IdHorario, $IdUsuario);
        $stmt = $cn->prepare("INSERT INTO solicitudes (estado, id_horario, id_usuario) VALUES (?, ?, ?)");
        $stmt->execute([$Estado, $IdHorario, $IdUsuario]);

        $cn->commit();
        Response::success("Solicitud creada correctamente", 200);
    } catch (DomainException $error) {
        if (isset($cn) && $cn->inTransaction()) $cn->rollBack();
        Response::error($error->getMessage(), -1004, 409);
    } catch (PDOException $error) {
        if (isset($cn) && $cn->inTransaction()) $cn->rollBack();
        Response::error("No se pudo crear la solicitud", -1003, 404);
    }
?>