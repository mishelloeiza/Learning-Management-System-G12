<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

    if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
        Response::error("Método no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $IdHorarios = trim($_POST["IdHorarios"] ?? '');

    if (empty($IdHorarios)) {
        Response::error("El Id del horario es obligatorio", -1002, 404);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn -> prepare("UPDATE horarios SET estado = 'cancelado' WHERE id_horarios = ?");
        $stmt -> execute([$IdHorarios]);

        if ($stmt -> rowCount() === 0) {
            Response::error("No se encontro el horario", -1003, 404);
        }

        Response::success("Horario cancelado correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo cancelar el horario", -1004, 500);
    }
?>