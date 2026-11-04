<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    $buscar = trim($_GET["buscar"] ?? '');
    $estado = trim($_GET["estado"] ?? '');
    $id_materia = trim($_GET["id_materia"] ?? '');

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("SELECT t.id_tutoria, t.titulo, t.descripcion, t.estado, t.fecha_inicio, t.fecha_fin, t.cupos,
            t.id_tutor, CONCAT(u.nombre, ' ', u.apellido) AS tutor, t.id_materia, m.nombre AS materia
            FROM tutorias t INNER JOIN usuarios u ON t.id_tutor = u.id_usuario
            INNER JOIN materias m ON t.id_materia = m.id_materia
            WHERE t.titulo LIKE ? AND (? = '' OR t.estado = ?) AND (? = '' OR t.id_materia = ?) ORDER BY t.id_tutoria DESC LIMIT 5");
        $stmt->execute(["%" . $buscar . "%", $estado, $estado, $id_materia, $id_materia]);

        $tutorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $horarios = [];

        if(!empty($tutorias)) {
            $ids = array_column($tutorias, "id_tutoria");
            $marcas = implode(",", array_fill(0, count($ids), "?"));

            $stmt = $cn->prepare("SELECT id_horarios AS id_horario, id_tutoria, dias_curso AS dia, hora_inicio, hora_fin, estado
                FROM horarios WHERE id_tutoria IN ($marcas) AND estado <> 'cancelado'
                ORDER BY FIELD(dias_curso,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), hora_inicio");
            $stmt->execute($ids);

            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $horarios[$fila["id_tutoria"]][] = $fila;
            }
        }

        foreach($tutorias as $i => $t) {
            $tutorias[$i]["horarios"] = $horarios[$t["id_tutoria"]] ?? [];
        }

        Response::success("Tutorias encontradas", 200, ["tutorias" => $tutorias]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las tutorias", -1002, 500);
    }
?>
