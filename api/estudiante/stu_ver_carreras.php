<?php
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET"){
        Response::error("Metodo no permitido", -1000, 405);
    }

    try {
        $cn = new Connection();

        $stmt = $cn->prepare("SELECT * FROM carreras");
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