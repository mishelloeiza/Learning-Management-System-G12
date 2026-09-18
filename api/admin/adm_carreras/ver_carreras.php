<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $buscar = trim($_GET["buscar"] ?? '');

    try {
        $cn = new Connection("admin");

        if($buscar == ""){
            $stmt = $cn->prepare("SELECT * FROM carreras ORDER BY id_carrera LIMIT 5");
            $stmt->execute();
        }else{
            $stmt = $cn->prepare("SELECT * FROM carreras WHERE nombre LIKE ? ORDER BY id_carrera");
            $stmt->execute(["%".$buscar."%"]);
        }

        $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($carreras)){
            Response::error("No se encontraron carreras", -1002, 404);
        }

        Response::success("Carreras encontradas", 200, ["carreras"=>$carreras]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar las carreras", -1003, 500);
    }
?>