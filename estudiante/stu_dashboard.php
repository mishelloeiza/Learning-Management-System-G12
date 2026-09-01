<?php
	//conexion
	include("../conexion.php");

	//Verificar sesion
	session_start();

	//Si no hay sesion iniciada o si el rol no es 1
	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1') {
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
	<h1>Estudiante</h1>
</body>
</html>