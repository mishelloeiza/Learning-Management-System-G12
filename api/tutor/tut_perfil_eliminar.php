<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");
    // PHP no rellena $_POST con PUT/DELETE; el cliente envía los campos codificados para formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $_POST);
    }

    if($_SERVER["REQUEST_METHOD"] !== "DELETE"){
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2'){
        Response::error("No autorizado", -1001, 401);
    }

    $id = $_SESSION['id'];

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare("UPDATE usuarios_2 SET activo = false WHERE id_usuario = ?");
        $stmt->execute([$id]);

        if($stmt->rowCount() === 0){
            Response::error("La cuenta no existe", -1002, 404);
        }

        $_SESSION = [];
        session_destroy();

        Response::success("Cuenta eliminada", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo eliminar la cuenta", -1003, 500);
    }
?>
