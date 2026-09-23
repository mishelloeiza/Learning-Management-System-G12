<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET"){
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2'){
        Response::error("No autorizado", -1001, 401);
    }

    try {
        $cn = new Connection("tutor");

        $stmt = $cn->prepare("SELECT id_carrera, nombre FROM carreras WHERE activo = true");
        $stmt->execute();
        $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($carreras == false){
            Response::error("No se encontraron carreras", -1002, 404);
        }

        Response::success("Carreras encontradas", 200, ["carreras"=>$carreras]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las carreras", -1003, 500);
    }
?>
