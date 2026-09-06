<?php
include("conexion.php");
$cn->set_charset("utf8mb4");

//si existe una sesion activa se cierra
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Crear Cuenta</title>
</head>

<body>
	<main>
		<h1>Crear Cuenta</h1>
		<form action="crear_cuenta.php" method="post">
			<label for="nombre">Nombre:</label>
			<input type="text" name="nombre" placeholder="Ingrese su nombre">

			<label for="apellido">Apellido:</label>
			<input type="text" name="apellido" placeholder="Ingrese su apellido">

			<label for="correo">Correo:</label>
			<input type="email" name="correo" placeholder="Ingrese su correo">

			<label for="telefono">Telefono:</label>
			<input type="tel" name="telefono" placeholder="Ingrese su telefono">

			<label for="contrasena">Contraseña:</label>
			<input type="password" name="contrasena" placeholder="Ingrese su contraseña">

			<button type="submit" name="btn">Crear cuenta</button>
		</form>
	</main>
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