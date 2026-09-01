<?php
	//conexion
	include("../conexion.php");

	//Verificar sesion
	session_start();

	//Si no hay sesion iniciada o si el rol no es 3
	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '3') {
	    header("Location: ../login.php");
	    exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h1>Administrador</h1>
	<a href="../cerrar_sesion.php">Cerrar sesión</a>
</body>
</html>