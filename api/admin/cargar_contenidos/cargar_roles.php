<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET"){
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3'){
        Response::error("No autorizado", -1001, 404);
    }

    try {
        $cn = new Connection("admin");

        $stmt = $cn->prepare("SELECT * FROM roles");
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($roles == false){
            Response::error("No se encontraron roles", -1002, 404);
        }

        Response::success("roles encontrados", 200, ["roles"=>$roles]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las roles", -1003, 500);
    }
?>