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
	<script src="../JS/navbar.js" defer></script>
</head>
<body>
	<header class="site-header">
		<nav class="navbar" aria-label="Navegación principal">
			<button class="navbar__toggle" type="button" aria-expanded="false" aria-controls="menuPrincipal" aria-label="Abrir menú">
				<span class="navbar__bars" aria-hidden="true"></span>
			</button>
			<ul class="navbar__menu" id="menuPrincipal">
				<li><a href="./adm_perfil.php">Mi perfil</a></li>
				<li><a href="./adm_dashboard.php">Inicio</a></li>
				<li><a href="./adm_carreras.php">Administrar Carreras</a></li>
				<li><a href="./adm_materias.php">Administrar Materias</a></li>
				<li><a href="./adm_tutorias.php">Administrar Tutorias</a></li>
				<li><a href="./adm_usuarios.php">Administrar Usuarios</a></li>
				<li><a href="./adm_horarios.php">Administrar Horarios</a></li>
				<li><a href="./adm_solicitudes.php">Administrar Solicitudes</a></li>
				<li><button class="navbar__action" id="CerrarS" type="button">Cerrar Sesión</button></li>
			</ul>
		</nav>
	</header>

	<main>
		<h1>Administrador</h1>
		<p>Resumen general del sistema de tutorías.</p>

		<section class="resumen-dashboard" aria-live="polite">

			<article class="tarjeta-resumen">
				<h3>Usuarios</h3>
				<p class="resumen-cantidad" data-stat="usuarios">–</p>
				<small data-desc="usuarios">Usuarios registrados</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Estudiantes</h3>
				<p class="resumen-cantidad" data-stat="estudiantes">–</p>
				<small data-desc="estudiantes">Cuentas de estudiantes</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Tutores</h3>
				<p class="resumen-cantidad" data-stat="tutores">–</p>
				<small data-desc="tutores">Cuentas de tutores</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Carreras</h3>
				<p class="resumen-cantidad" data-stat="carreras">–</p>
				<small data-desc="carreras">Carreras registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Materias</h3>
				<p class="resumen-cantidad" data-stat="materias">–</p>
				<small data-desc="materias">Materias registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Tutorías</h3>
				<p class="resumen-cantidad" data-stat="tutorias">–</p>
				<small data-desc="tutorias">Tutorías registradas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Horarios</h3>
				<p class="resumen-cantidad" data-stat="horarios">–</p>
				<small data-desc="horarios">Horarios registrados</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes</h3>
				<p class="resumen-cantidad" data-stat="solicitudes">–</p>
				<small data-desc="solicitudes">Solicitudes recibidas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes pendientes</h3>
				<p class="resumen-cantidad" data-stat="solicitudes_pendientes">–</p>
				<small data-desc="solicitudes_pendientes">Esperando respuesta del tutor</small>
			</article>

		</section>
	</main>

	<script>

		//Estadisticas de las tarjetas
		async function cargarEstadisticas() {
			try {
				const respuesta = await fetch("../api/admin/adm_estadisticas.php");
				const resultado = await respuesta.json();

				if (resultado.code !== 200) {
					console.error(resultado.message);
					return;
				}

				const e = resultado.estadisticas;
				document.querySelectorAll("[data-stat]").forEach(function(el) {
					el.textContent = e[el.dataset.stat] ?? "–";
				});
				document.querySelector('[data-desc="tutorias"]').textContent = e.tutorias_vigentes + " vigentes (activas o en curso)";
			} catch (error) {
				console.error(error);
			}
		}

		cargarEstadisticas();

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