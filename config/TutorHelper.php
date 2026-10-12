<?php
    require_once(__DIR__ . "/Connection.php");
    require_once(__DIR__ . "/Response.php");

    class TutorHelper
    {
        const MAX_HORARIOS = 5;
        const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

        static function iniciar(string $metodo): string
        {
            if ($_SERVER["REQUEST_METHOD"] !== $metodo) {
                Response::error("Metodo no permitido", -1000, 405);
            }
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2' || !isset($_SESSION['id'])) {
                Response::error("No autorizado", -1001, 403);
            }
            return (string) $_SESSION['id'];
        }

        static function sincronizarEstados(PDO $cn, string $idTutor): void
        {
            $hoy = date("Y-m-d");
            $cn->prepare("UPDATE tutorias SET estado = 'en curso' WHERE id_tutor = ? AND estado = 'activa' AND ? BETWEEN fecha_inicio AND fecha_fin")
                ->execute([$idTutor, $hoy]);
            $cn->prepare("UPDATE tutorias SET estado = 'finalizada' WHERE id_tutor = ? AND estado IN ('activa','en curso') AND fecha_fin < ?")
                ->execute([$idTutor, $hoy]);
            $cn->prepare("UPDATE horarios h INNER JOIN tutorias t ON h.id_tutoria = t.id_tutoria SET h.estado = 'finalizado'
                WHERE t.id_tutor = ? AND t.estado = 'finalizada' AND h.estado IN ('disponible','asignado')")
                ->execute([$idTutor]);
        }

        static function fechaValida(string $fecha): bool
        {
            $d = DateTime::createFromFormat("Y-m-d", $fecha);
            return $d && $d->format("Y-m-d") === $fecha;
        }

        static function leerTutoria(): array
        {
            $titulo = trim($_POST["titulo"] ?? '');
            $descripcion = trim($_POST["descripcion"] ?? '');
            $idMateria = trim($_POST["id_materia"] ?? '');
            $cupos = trim($_POST["cupos"] ?? '');
            $fechaInicio = trim($_POST["fecha_inicio"] ?? '');
            $fechaFin = trim($_POST["fecha_fin"] ?? '');

            if ($titulo === '' || $descripcion === '' || $idMateria === '' || $cupos === '' || $fechaInicio === '' || $fechaFin === '') {
                Response::error("Todos los campos son obligatorios", -1100, 400);
            }
            if (preg_match_all('/./su', $titulo) > 255 || preg_match_all('/./su', $descripcion) > 255) {
                Response::error("El titulo y la descripcion no pueden superar los 255 caracteres", -1101, 400);
            }
            if (!ctype_digit($idMateria) || !ctype_digit($cupos) || (int) $cupos < 1 || (int) $cupos > self::MAX_HORARIOS) {
                Response::error("Materia o cupos invalidos (los cupos van de 1 a " . self::MAX_HORARIOS . ")", -1102, 400);
            }
            if (!self::fechaValida($fechaInicio) || !self::fechaValida($fechaFin)) {
                Response::error("Fechas invalidas", -1103, 400);
            }
            if ($fechaFin <= $fechaInicio) {
                Response::error("La fecha de fin debe ser posterior a la fecha de inicio", -1104, 400);
            }

            return [
                "titulo" => $titulo,
                "descripcion" => $descripcion,
                "id_materia" => (int) $idMateria,
                "cupos" => (int) $cupos,
                "fecha_inicio" => $fechaInicio,
                "fecha_fin" => $fechaFin,
            ];
        }

        static function leerHorarios(): array
        {
            $horarios = json_decode($_POST["horarios"] ?? '', true);

            if (!is_array($horarios) || count($horarios) < 1 || count($horarios) > self::MAX_HORARIOS) {
                Response::error("Debe registrar entre 1 y " . self::MAX_HORARIOS . " horarios", -1110, 400);
            }

            $resultado = [];
            foreach ($horarios as $h) {
                $dia = is_array($h) ? trim((string) ($h["dia"] ?? '')) : '';
                $inicio = is_array($h) ? trim((string) ($h["hora_inicio"] ?? '')) : '';
                $id = is_array($h) ? trim((string) ($h["id_horario"] ?? '')) : '';

                if (!in_array($dia, self::DIAS, true)) {
                    Response::error("Dia de horario invalido", -1111, 400);
                }
                if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $inicio)) {
                    Response::error("Hora de inicio invalida (formato HH:MM)", -1112, 400);
                }
                if ($inicio >= "23:00") {
                    Response::error("Cada horario dura 1 hora: debe iniciar antes de las 23:00", -1113, 400);
                }
                if ($id !== '' && !ctype_digit($id)) {
                    Response::error("Horario invalido", -1114, 400);
                }

                $fin = (new DateTime("2000-01-01 " . $inicio))->modify("+1 hour")->format("H:i:s");
                $resultado[] = ["id" => $id === '' ? null : (int) $id, "dia" => $dia, "inicio" => $inicio . ":00", "fin" => $fin];
            }

            for ($i = 0; $i < count($resultado); $i++) {
                for ($j = $i + 1; $j < count($resultado); $j++) {
                    if ($resultado[$i]["dia"] === $resultado[$j]["dia"]
                        && $resultado[$i]["inicio"] < $resultado[$j]["fin"] && $resultado[$j]["inicio"] < $resultado[$i]["fin"]) {
                        Response::error("Hay horarios que se traslapan el mismo dia", -1115, 400);
                    }
                }
            }
            return $resultado;
        }

        static function esEspecialista(PDO $cn, string $idTutor, int $idMateria): bool
        {
            $stmt = $cn->prepare("SELECT 1 FROM materias m INNER JOIN usuarios_2 u ON m.id_carrera = u.id_carrera
                WHERE u.id_usuario = ? AND m.id_materia = ? AND m.activo = true");
            $stmt->execute([$idTutor, $idMateria]);
            return (bool) $stmt->fetchColumn();
        }

        static function hayChoque(PDO $cn, string $idTutor, ?int $idTutoria, array $h, string $fechaInicio, string $fechaFin): bool
        {
            $stmt = $cn->prepare("SELECT 1 FROM horarios hr INNER JOIN tutorias t ON hr.id_tutoria = t.id_tutoria
                WHERE t.id_tutor = ? AND t.id_tutoria <> ? AND t.estado IN ('activa','en curso') AND hr.estado IN ('disponible','asignado')
                AND hr.dias_curso = ? AND hr.hora_inicio < ? AND hr.hora_fin > ?
                AND t.fecha_inicio <= ? AND t.fecha_fin >= ? LIMIT 1");
            $stmt->execute([$idTutor, $idTutoria ?? 0, $h["dia"], $h["fin"], $h["inicio"], $fechaFin, $fechaInicio]);
            return (bool) $stmt->fetchColumn();
        }

        static function horariosDe(PDO $cn, array $idsTutoria): array
        {
            if (empty($idsTutoria)) {
                return [];
            }
            $marcas = implode(",", array_fill(0, count($idsTutoria), "?"));
            $stmt = $cn->prepare("SELECT h.id_horarios AS id_horario, h.id_tutoria, h.dias_curso AS dia, h.hora_inicio, h.hora_fin, h.estado,
                s.id_solicitud, s.estado AS estado_solicitud, CONCAT(u.nombre, ' ', u.apellido) AS estudiante
                FROM horarios h
                LEFT JOIN solicitudes s ON s.id_horario = h.id_horarios AND s.estado IN ('pendiente','aprobada')
                LEFT JOIN estudiantes_info u ON s.id_usuario = u.id_usuario
                WHERE h.id_tutoria IN ($marcas) AND h.estado <> 'cancelado'
                ORDER BY FIELD(h.dias_curso,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), h.hora_inicio");
            $stmt->execute($idsTutoria);

            $porTutoria = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $porTutoria[$fila["id_tutoria"]][] = $fila;
            }
            return $porTutoria;
        }

        static function leerFecha(string $clave): string
        {
            $valor = trim($_GET[$clave] ?? '');
            if ($valor !== '' && !self::fechaValida($valor)) {
                Response::error("Fecha invalida en el filtro", -1120, 400);
            }
            return $valor;
        }

        static function consultarTutorias(PDO $cn, string $idTutor, string $estado, string $idMateria, string $desde, string $hasta): array
        {
            $stmt = $cn->prepare("SELECT t.id_tutoria, t.titulo, t.descripcion, t.estado, t.fecha_inicio, t.fecha_fin, t.cupos,
                t.id_materia, m.nombre AS materia,
                (SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
                    WHERE h.id_tutoria = t.id_tutoria AND s.estado = 'aprobada') AS aprobadas,
                (SELECT COUNT(*) FROM solicitudes s INNER JOIN horarios h ON s.id_horario = h.id_horarios
                    WHERE h.id_tutoria = t.id_tutoria AND s.estado = 'pendiente') AS pendientes
                FROM tutorias t INNER JOIN materias m ON t.id_materia = m.id_materia
                WHERE t.id_tutor = ? AND (? = '' OR t.estado = ?) AND (? = '' OR t.id_materia = ?)
                AND (? = '' OR t.fecha_inicio >= ?) AND (? = '' OR t.fecha_fin <= ?)
                ORDER BY t.fecha_inicio, t.id_tutoria");
            $stmt->execute([$idTutor, $estado, $estado, $idMateria, $idMateria, $desde, $desde, $hasta, $hasta]);
            $tutorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $horarios = self::horariosDe($cn, array_column($tutorias, "id_tutoria"));
            foreach ($tutorias as &$t) {
                $t["horarios"] = $horarios[$t["id_tutoria"]] ?? [];
                $t["abierta"] = in_array($t["estado"], ["activa", "en curso"], true) && (int) $t["aprobadas"] < (int) $t["cupos"];
            }
            return $tutorias;
        }
    }
?>
