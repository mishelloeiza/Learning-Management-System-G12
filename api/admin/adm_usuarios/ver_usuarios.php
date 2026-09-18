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
    $id_carrera = trim($_GET["id_carrera"] ?? '');
    $id_rol = trim($_GET["id_rol"] ?? '');

    try {
        $cn = new Connection("admin");

        if($buscar == "" && $id_carrera == "" && $id_rol == "") {
            $stmt = $cn->prepare("SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.id_carrera, c.nombre AS carrera,
            u.id_rol, r.nombre AS rol, u.activo FROM usuarios u INNER JOIN carreras c ON u.id_carrera = c.id_carrera
            INNER JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.id_usuario LIMIT 5");
            $stmt->execute();
        }else{
            $stmt = $cn->prepare("SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.id_carrera, c.nombre AS carrera,
            u.id_rol, r.nombre AS rol, u.activo FROM usuarios u INNER JOIN carreras c ON u.id_carrera = c.id_carrera
            INNER JOIN roles r ON u.id_rol = r.id_rol WHERE u.correo LIKE ?
            AND (? = '' OR u.id_carrera = ?) AND (? = '' OR u.id_rol = ?) ORDER BY u.id_usuario");

            $stmt->execute(["%" . $buscar . "%", $id_carrera, $id_carrera, $id_rol, $id_rol]);
        }

        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($usuarios)){
            Response::error("No se encontraron usuarios", -1002, 404);

        }

        Response::success("Usuarios encontradas", 200, ["usuarios"=>$usuarios]);
    } catch (PDOException $error) {
        Response::error("No se pudieron cargar los usuarios", -1003, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>