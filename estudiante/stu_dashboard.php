<?php
	require_once("../api/estudiante/stu_verificar_sesion.php");
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
					<a href="./stu_perfil.php">Mi perfil</a>	
				</li>
				<li>
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
			</ul>
		</nav>	
	</header>
	<main>
		<h1>Estudiante</h1>
	</main>

	<script>
		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/estudiante/stu_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json(); 

				if (resultado.code = 200){
					window.location.href = "../prin_dashboard.php";
				} else {
					alert(resultado.message); 
				}
			
			} catch (error) {
				console.error(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		}); 
	</script>
</body>
</html>