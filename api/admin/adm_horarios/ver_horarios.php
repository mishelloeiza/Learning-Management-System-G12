<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $BuscarDia = trim($_GET["buscardia"] ?? '');
    $BuscarMateria = trim($_GET["buscarmateria"] ?? '');

    try {
        $cn = new Connection("admin");

        $stmt = $cn -> prepare("SELECT h.id_horarios, h.hora_inicio, h.hora_fin, h.dias_curso, h.estado, t.id_tutoria, m.nombre as materia 
        FROM horarios as h INNER JOIN tutorias as t ON h.id_tutoria = t.id_tutoria INNER JOIN materias as m ON t.id_materia = m.id_materia 
        WHERE (? = '' OR h.dias_curso LIKE ?) AND (? = '' OR m.nombre LIKE ?) ORDER BY h.id_horarios");

        $stmt -> execute([$BuscarDia, "%".$BuscarDia."%", $BuscarMateria, "%".$BuscarMateria."%"]);            

        $Horarios = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        if (empty($Horarios)) {
            Response::error("No se encontraron horarios", -1002, 404);
        }

        Response::success("Horario encontrados", 200, ["horarios" => $Horarios]);
    } catch (PDOException $error) {
        Response::error("No se puedieron cargar los horario", -1003, 500);
    }
?>