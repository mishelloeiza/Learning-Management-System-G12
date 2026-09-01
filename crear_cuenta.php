<?php 
	include("conexion.php");

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
	if(isset($_POST['btn'])){
		$nombre = $cn->real_escape_string($_POST['nombre']);
	  	$apellido = $cn->real_escape_string($_POST['apellido']);
	  	$correo = $cn->real_escape_string($_POST['correo']);
	  	$telefono = $cn->real_escape_string($_POST['telefono']);
	  	$contrasena = $cn->real_escape_string($_POST['contrasena']);
		
		//Cifrar la contraseña
		$cifrada = password_hash($contrasena, PASSWORD_DEFAULT);

		//Siempre el rol sera 1 porque es el rol de los estudiantes; 	
		$sql = "insert into usuarios(nombre, apellido, correo, telefono, contrasena, id_rol) values ('$nombre','$apellido','$correo','$telefono','$cifrada','1')";

	    mysqli_query($cn, $sql);
	    echo "<script>alert('Cuenta Creada Correctamente')</script>"; 
	
	}
?>