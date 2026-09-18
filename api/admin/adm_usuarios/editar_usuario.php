<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no permitido", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
        Response::error("No autorizado", -1001, 404);
    }

    $id_usuario = trim($_POST["id_usuario"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? '');
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");
    $rol = trim($_POST["rol"] ?? "");
    $carrera = trim($_POST["carrera"] ?? "");
    $activo = trim($_POST["activo"] ?? 0);

    if(empty($id_usuario) || empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($rol) || empty($carrera)){
        Response::error("Todos los campos excepto contraseña son obligatorios", -1002, 400);
    }

    //Validar correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        Response::error("Correo invalido", -1002, 400);
    }

    //Validar telefono
    if (!ctype_digit($telefono) || strlen($telefono) > 9) {
        Response::error("Telefono invalido", -1003, 400);
    }

    //Cifrar contraseña
    $cifrada = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        $cn = new Connection();

        if(empty($contrasena)){
            $stmt = $cn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, activo = ?, 
            id_rol = ?, id_carrera = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre, $apellido, $correo, $telefono, $activo, $rol, $carrera, $id_usuario]);
        }else {
            //Validar contraseña
            if (strlen($contrasena) < 8) {
                Response::error("La contraseña debe tener al menos 8 caracteres", -1004, 400);
            }

            $stmt = $cn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, contrasena = ?, activo = ?, 
            id_rol = ?, id_carrera = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre, $apellido, $correo, $telefono, $cifrada, $activo, $rol, $carrera, $id_usuario]);
        }
        if($stmt->rowCount() === 0) {
            Response::error("No se encontro o no se modifico el usuario", -1003, 404);
        }

        Response::success("Usuario modificada correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo modificar el usuario", -1004, 500);
    }
?>