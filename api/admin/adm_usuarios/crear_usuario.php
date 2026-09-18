<?php
    require_once(__DIR__ . "/../../../config/Connection.php");
    require_once(__DIR__ . "/../../../config/Response.php");

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3'){
        Response::error("No autorizado", -1001, 404);
    }

    $nombre = trim($_POST["nombre"] ?? '');
    $apellido = trim($_POST["apellido"] ?? '');
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");
    $rol = trim($_POST["rol"] ?? "");
    $carrera = trim($_POST["carrera"] ?? "");

    if(empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($contrasena) || empty($rol) || empty($carrera)){
        Response::error("Todos los campos son obligatorios", -1002, 404);
    }

    //Validar correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        Response::error("Correo invalido", -1002, 400);
    }

    //Validar telefono
    if (!ctype_digit($telefono) || strlen($telefono) > 9) {
        Response::error("Telefono invalido", -1003, 400);
    }

    //Validar contraseña
    if (strlen($contrasena) < 8) {
        Response::error("La contraseña debe tener al menos 8 caracteres", -1004, 400);
    }

    //Cifrar contraseña
    $cifrada = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        $cn = new Connection();

        $stmt = $cn->prepare("INSERT INTO usuarios (nombre, apellido, correo, telefono, contrasena, id_rol, id_carrera) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $correo, $telefono, $cifrada, $rol, $carrera]);

        Response::success("Usuario creado correctamente", 200);
    } catch (PDOException $error) {
        Response::error("No se pudo crear el usuario", -1003, 404);
        //Response::debug($error->getMessage(), -1004, 500);
    }
?>