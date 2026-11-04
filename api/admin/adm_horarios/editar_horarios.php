<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

function validarHorario(PDO $cn, string $idTutoria, string $dia, string $inicio, string $fin, int $excluir = 0): void
{
    if (!ctype_digit($idTutoria) || !in_array($dia, ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'], true)
        || !preg_match('/^([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $inicio)
        || !preg_match('/^([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $fin)
        || $fin <= $inicio) {
        throw new DomainException('El horario o sus horas son invalidos');
    }
    $stmt = $cn->prepare('SELECT id_tutor, fecha_inicio, fecha_fin, estado FROM tutorias WHERE id_tutoria = ?');
    $stmt->execute([$idTutoria]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$t || !in_array($t['estado'], ['activa','en curso'], true) || $t['fecha_fin'] < date('Y-m-d')) {
        throw new DomainException('La tutoria no esta disponible');
    }
    $stmt = $cn->prepare("SELECT 1 FROM horarios h INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria
        WHERE t.id_tutor = ? AND h.id_horarios <> ? AND h.dias_curso = ?
        AND h.hora_inicio < ? AND h.hora_fin > ? AND h.estado IN ('disponible','asignado')
        AND t.estado IN ('activa','en curso') AND t.fecha_inicio <= ? AND t.fecha_fin >= ? LIMIT 1");
    $stmt->execute([$t['id_tutor'], $excluir, $dia, $fin, $inicio, $t['fecha_fin'], $t['fecha_inicio']]);
    if ($stmt->fetchColumn()) {
        throw new DomainException('El tutor ya tiene un horario que se traslapa');
    }
    $dias = ['domingo'=>0,'lunes'=>1,'martes'=>2,'miercoles'=>3,'jueves'=>4,'viernes'=>5,'sabado'=>6];
    $primero = new DateTimeImmutable($t['fecha_inicio']);
    $ultimo = new DateTimeImmutable($t['fecha_fin']);
    $diferencia = ($dias[$dia] - (int) $primero->format('w') + 7) % 7;
    if ($primero->modify('+' . $diferencia . ' days') > $ultimo) {
        throw new DomainException('El dia elegido no ocurre dentro de la tutoria');
    }
}

    if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $IdHorarios = trim($_POST["IdHorarios"] ?? '');
    $HoraInicio = trim($_POST["HoraInicio"] ?? '');
    $HoraFin = trim($_POST["HoraFin"] ?? '');
    $DiasCurso = trim($_POST["DiasCurso"] ?? '');
    $Estado = trim($_POST["Estado"] ?? '');
    $IdTutoria = trim($_POST["IdTutoria"] ?? '');

    if (empty($IdHorarios) || empty($HoraInicio) || empty($HoraFin) || empty($DiasCurso) || empty($IdTutoria)) {
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    if (!in_array($Estado, ["disponible", "asignado", "finalizado", "cancelado"], true)) {
        Response::error("Estado invalido", -1005, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("SELECT h.*, (SELECT COUNT(*) FROM solicitudes s WHERE s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')) AS ocupadas
            FROM horarios h WHERE h.id_horarios = ?");
        $stmt->execute([$IdHorarios]);
        $anterior = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$anterior) {
            Response::error("No se encontro el horario", -1003, 404);
        }
        if ((int) $anterior['ocupadas'] > 0 && ($HoraInicio !== substr($anterior['hora_inicio'],0,5) && $HoraInicio !== $anterior['hora_inicio']
            || $HoraFin !== substr($anterior['hora_fin'],0,5) && $HoraFin !== $anterior['hora_fin']
            || $DiasCurso !== $anterior['dias_curso'] || $IdTutoria != $anterior['id_tutoria'] || $Estado !== $anterior['estado'])) {
            Response::error("No puede modificar un horario con solicitudes activas", -1006, 409);
        }
        if (in_array($Estado, ['disponible','asignado'], true)) {
            validarHorario($cn, $IdTutoria, $DiasCurso, $HoraInicio, $HoraFin, (int) $IdHorarios);
        }
        $stmt = $cn->prepare("UPDATE horarios SET hora_inicio = ?, hora_fin = ?, dias_curso = ?, estado = ?, id_tutoria = ? WHERE id_horarios = ?");
        $stmt->execute([$HoraInicio, $HoraFin, $DiasCurso, $Estado, $IdTutoria, $IdHorarios]);

        Response::success("Horario editado correctamente", 200);
    } catch (DomainException $error) {
        Response::error($error->getMessage(), -1007, 409);
    } catch (PDOException $error) {
        Response::error("No se pudo modificar el horario", -1004, 500);
    }
?>