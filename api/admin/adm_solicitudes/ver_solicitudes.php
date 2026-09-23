<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $Estado = trim($_GET["estado"] ?? '');
    $Materia = trim($_GET["materia"] ?? '');
    $FechaInicio = trim($_GET["fechainicio"] ?? '');
    $FechaFin = trim($_GET["fechafin"] ?? '');

    $FechaInicio = ($FechaInicio === '') ? null : $FechaInicio;
    $FechaFin = ($FechaFin === '') ? null : $FechaFin;

    try {
        $cn = new Connection("admin");

        $stmt = $cn -> prepare("SELECT s.id_solicitud, s.estado, s.fecha_solicitud, s.fecha_respuesta, s.id_horario, s.id_usuario, m.nombre AS materia
            FROM solicitudes AS s JOIN horarios AS h ON s.id_horario = h.id_horarios JOIN tutorias AS t ON h.id_tutoria = t.id_tutoria
            JOIN materias AS m ON t.id_materia = m.id_materia WHERE (? = '' OR s.estado LIKE ?) AND (? = '' OR m.nombre LIKE ?) 
            AND (? IS NULL OR DATE(s.fecha_solicitud) >= ?) AND (? IS NULL OR DATE(s.fecha_solicitud) <= ?) ORDER BY s.id_solicitud");

        $stmt -> execute([$Estado, "%".$Estado."%", $Materia, "%".$Materia."%", $FechaInicio, $FechaInicio, $FechaFin, $FechaFin]);

        $Solicitudes = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        if(empty($Solicitudes)){
            Response::error("No se encontraron solicitudes", -1002, 404);
        }

        Response::success("Solicitudes encontradas", 200, ["solicitudes"=>$Solicitudes]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las solicitudes", -1003, 500);
    }
?>