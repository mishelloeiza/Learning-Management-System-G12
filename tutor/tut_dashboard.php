<?php
	//conexion
	include("../conexion.php");

	session_start();

	
	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2') {
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
		<header>
		<nav>
			<ul>
				<li>
					<a href="./tut_perfil.php">Mi perfil</a>	
				</li>
				<li>
					<a href="../cerrar_sesion.php">Cerrar sesión</a>
				</li>
			</ul>
		</nav>	
	</header>
	<main>
		<h1>Tutor</h1>
	</main>
</body>
</html>