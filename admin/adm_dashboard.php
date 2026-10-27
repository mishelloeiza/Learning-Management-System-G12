<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
	<link rel="stylesheet" href="../CSS/tarjetas.css">
	<link rel="stylesheet" href="../CSS/navbar.css">
</head>
<body>
	<header>
		<nav>
			<ul>
				<li>
					<a href="./adm_perfil.php">Mi perfil</a>
				</li>
				<li>
                    <a href="./adm_dashboard.php">Inicio</a>
                </li>
				<li>
					<a href="./adm_carreras.php">Administrar Carreras</a>
				</li>
				<li>
					<a href="./adm_materias.php">Administrar Materias</a>
				</li>
				<li>
					<a href="./adm_tutorias.php">Administrar Tutorias</a>
				</li>
				<li>
					<a href="./adm_usuarios.php">Administrar Usuarios</a>
				</li>
				<li>
					<a href="./adm_horarios.php">Administrar Horarios</a>
				</li>
				<li>
					<a href="./adm_solicitudes.php">Administrar Solicitudes</a>
				</li>
				<li>
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
			</ul>
		</nav>
	</header>

	<main>
		<h1>Administrador</h1>
		<p>Resumen general del sistema de tutorías.</p>

		<section class="resumen-dashboard">

			<article class="tarjeta-resumen">
				<h3>Usuarios</h3>
				<p class="resumen-cantidad">0</p>
				<small>Usuarios registrados</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Carreras</h3>
				<p class="resumen-cantidad">0</p>
				<small>Carreras registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Materias</h3>
				<p class="resumen-cantidad">0</p>
				<small>Materias registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Tutorías</h3>
				<p class="resumen-cantidad">0</p>
				<small>Tutorías registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Horarios</h3>
				<p class="resumen-cantidad">0</p>
				<small>Horarios registrados</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes</h3>
				<p class="resumen-cantidad">0</p>
				<small>Solicitudes recibidas</small>
			</article>

		</section>
	</main>

	<script>

		//Cerrar sesion
		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/admin/adm_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json(); 

				if (resultado.code === 200){
					window.location.href = "../index.php";
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