<?php
//conexion
include("conexion.php");
$cn->set_charset("utf8mb4");

//si existe una sesion activa se cierra
session_start();
session_unset();
session_destroy();

//abrir nueva sesion
session_start();

//Verificacion login
if (isset($_POST['btn'])) {
	$usuario = $_POST['usuario'];
	$contrasena = $_POST['contrasena'];

	//Sentencia SQL preparada
	$stmt = $cn->prepare("SELECT correo, contrasena, id_rol FROM usuarios WHERE correo = ?");
	$stmt->bind_param("s", $usuario);
	$stmt->execute();
	$arrayb = $stmt->get_result()->fetch_assoc();

	if ($arrayb !== null && password_verify($contrasena, $arrayb['contrasena'])) {
		session_regenerate_id(true);
		//Definir rol de la sesion
		$_SESSION['rol'] = (string) $arrayb['id_rol'];

		if ($_SESSION['rol'] === '3') {
			header("Location: ./admin/adm_dashboard.php");
			echo "<script>alert('Iniciando Sesión')</script>";
		} else if ($_SESSION['rol'] === '2') {
			header("Location: ./tutor/tut_dashboard.php");
		} else if ($_SESSION['rol'] === '1') {
			header("Location: ./estudiante/stu_dashboard.php");
		}
		//Detener php
		exit();    
	} else {
		echo "<script>alert('Contraseña o Usuario incorrectos.')</script>";
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
</head>

<body>
	<main>
		<h1>Iniciar Sesión</h1>
		<form action="login.php" method="post">
			<label for="usuario">Correo:</label>
			<input type="text" name="usuario" placeholder="Ingrese su correo">
			<label for="contrasena">Contraseña:</label>
			<input type="password" name="contrasena" placeholder="Ingrese su contraseña">
			<button type="submit" name="btn">Ingresar</button>

			<a href="crear_cuenta.php" title="Crear cuenta">Crear Cuenta</a>
		</form>
	</main>
</body>

</html>

<?php

?>