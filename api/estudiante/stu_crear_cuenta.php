<?php
    //Usar archivos de response y conexion
    require_once(__DIR__ . "/config_stu/Connection.php");
    require_once(__DIR__ . "/config_stu/Response.php");

    //Solo aceptar solicitudes POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error("Metodo no permitido", -1000, 405);
    }

    //Recibir datos
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $carrera = trim($_POST['carrera'] ??'');
    $contrasena = $_POST['contrasena'] ?? '';

    //Validar campos vacios
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($carrera) ||empty($contrasena)) {
        Response::error("Todos los campos son obligatorios", -1001, 400);
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
        //Consulta preparada
        $stmt = $cn->prepare("INSERT INTO usuarios_1 (nombre, apellido, correo, telefono, contrasena, id_carrera, id_rol) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$nombre, $apellido, $correo, $telefono, $cifrada, $carrera]);
        //Llamar a response
        Response::success("Cuenta creada correctamente", 201);
        exit();
    } catch (PDOException $error) {
        //Correo duplicado con el codigo 1062 de sql
        if ($error->errorInfo[1] == 1062) {
            Response::error("Ese correo ya está registrado", -1005, 409);
        }
        //Cualquier otro error
        Response::error("No se pudo crear la cuenta, intenta de nuevo", -1006, 500);
    }
?>
