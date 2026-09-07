<?php
include("conexion.php");
$cn->set_charset("utf8mb4");

//si existe una sesion activa se cierra
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Crear Cuenta</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="auth.css">
</head>

<body>
	<div class="auth-page">
		<div class="auth-card">
			<div class="auth-card-bar"></div>
			<main class="auth-form">
				<h2>Crear cuenta</h2>
				<p class="subtitle">Completá tus datos para registrarte.</p>

				<form action="crear_cuenta.php" method="post">
					<div class="field">
						<label for="nombre">Nombre</label>
						<input type="text" id="nombre" name="nombre" placeholder="Ingrese su nombre">
					</div>

					<div class="field">
						<label for="apellido">Apellido</label>
						<input type="text" id="apellido" name="apellido" placeholder="Ingrese su apellido">
					</div>

					<div class="field">
						<label for="correo">Correo</label>
						<input type="email" id="correo" name="correo" placeholder="Ingrese su correo">
					</div>

					<div class="field">
						<label for="telefono">Teléfono</label>
						<input type="tel" id="telefono" name="telefono" placeholder="Ingrese su teléfono">
					</div>

					<div class="field">
						<label for="contrasena">Contraseña</label>
						<input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña">
					</div>

					<button type="submit" name="btn" class="btn-primary">Crear cuenta</button>

					<p class="auth-switch">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></p>
				</form>
			</main>
		</div>
	</div>
</body>

</html>

<?php
if (isset($_POST['btn'])) {
	$nombre = trim($_POST['nombre']);
	$apellido = trim($_POST['apellido']);
	$correo = trim($_POST['correo']);
	$telefono = trim($_POST['telefono']);
	$contrasena = $_POST['contrasena'];

	//Validaciones de campos
	if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
		echo "<script>alert('Correo inválido')</script>";
	} else if (strlen($telefono) > 9 || !ctype_digit($telefono)) {
		echo "<script>alert('Teléfono inválido')</script>";
	} else if (strlen($contrasena < 8)) {
		echo "<script>alert('La contraseña debe tener al menos 8 caracteres')</script>";
	} else {
		//Cifrar la contraseña
		$cifrada = password_hash($contrasena, PASSWORD_DEFAULT);

		try {
			//Sentencia SQL preparada
			$stmt = $cn->prepare("INSERT INTO usuarios (nombre, apellido, correo, telefono, contrasena, id_rol) VALUES (?, ?, ?, ?, ?, 1)");
			$stmt->bind_param("sssss", $nombre, $apellido, $correo, $telefono, $cifrada);
			$stmt->execute();
			echo "<script>alert('Cuenta creada correctamente')</script>";
		} catch (mysqli_sql_exception $error) {
			if ($error->getCode() == 1062) {
				echo "<script>alert('Ese correo ya está registrado')</script>";
			} else {
				echo "<script>alert('No se puedo crea la cuenta, intenta de nuevo')</script>";
			}
		}
	}
}
?>