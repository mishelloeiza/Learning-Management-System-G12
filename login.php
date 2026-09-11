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
	$stmt = $cn->prepare("SELECT id_usuario, correo, contrasena, id_rol FROM usuarios WHERE correo = ?");
	$stmt->bind_param("s", $usuario);
	$stmt->execute();
	$arrayb = $stmt->get_result()->fetch_assoc();

	if ($arrayb !== null && password_verify($contrasena, $arrayb['contrasena'])) {
		session_regenerate_id(true);
		//Definir rol de la sesion
		$_SESSION['rol'] = (string) $arrayb['id_rol'];
		//Definir id de la sesion
		$_SESSION['id'] = (string) $arrayb['id_usuario'];

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
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="auth.css">
</head>

<body>
	<div class="auth-page">
		<div class="auth-card">
			<div class="auth-card-bar"></div>
			<main class="auth-form">
				<h2>Iniciar sesión</h2>
				<p class="subtitle">Ingresá con tu correo institucional.</p>

				<form action="login.php" method="post">
					<div class="field">
						<label for="usuario">Correo</label>
						<input type="text" id="usuario" name="usuario" placeholder="Ingrese su correo">
					</div>

					<div class="field">
						<label for="contrasena">Contraseña</label>
						<input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña">
					</div>

					<button type="submit" name="btn" class="btn-primary">Ingresar</button>

					<p class="auth-switch"><a href="crear_cuenta.php" title="Crear cuenta">Crear cuenta</a></p>
				</form>
			</main>
		</div>
	</div>
</body>

</html>
