<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 403);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("SELECT id_usuario, nombre, apellido, activo FROM usuarios WHERE id_rol = 2 ORDER BY nombre, apellido");
        $stmt->execute();
        $tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($tutores)) {
            Response::error("No se encontraron tutores", -1002, 404);
        }

        Response::success("Tutores encontrados", 200, ["tutores" => $tutores]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar los tutores", -1003, 500);
    }
?>
