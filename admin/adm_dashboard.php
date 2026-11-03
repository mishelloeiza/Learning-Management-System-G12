<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Adm Dashboard | Campus Virtual G12</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
	<link rel="stylesheet" href="../CSS/tarjetas.css">
	<link rel="stylesheet" href="../CSS/navbar.css">

</head>
<body>
<script>
    // El token lo genera Csrf.php; el servidor lo comprueba en cada operación.
    const apiCsrfToken = <?php require_once __DIR__ . '/../config/Csrf.php'; echo json_encode(Csrf::token()); ?>;
    function apiFetch(url, options = {}) {
        if (!['GET', 'HEAD', 'OPTIONS'].includes((options.method || 'GET').toUpperCase())) {
            options = { ...options, headers: { ...(options.headers || {}), 'X-CSRF-Token': apiCsrfToken } };
        }
        return fetch(url, options);
    }
</script>

	<header class="site-header">
		<nav class="navbar" aria-label="Navegación principal">
            <span class="navbar__brand" aria-label="LMS G12"><span class="navbar__brand-mark" aria-hidden="true">G12</span><span class="navbar__brand-name">Campus<span>Virtual</span></span></span>
			<input class="navbar__check" id="navToggle" type="checkbox" aria-label="Mostrar menú de navegación">
			<label class="navbar__toggle" for="navToggle" aria-label="Abrir o cerrar menú">
			<span class="navbar__bars" aria-hidden="true"></span>
			</label>
			<ul class="navbar__menu" id="menuPrincipal">
				<li><a href="./adm_perfil">Mi perfil</a></li>
				<li><a href="./adm_dashboard">Inicio</a></li>
				<li><a href="./adm_carreras">Administrar Carreras</a></li>
				<li><a href="./adm_materias">Administrar Materias</a></li>
				<li><a href="./adm_tutorias">Administrar Tutorias</a></li>
				<li><a href="./adm_usuarios">Administrar Usuarios</a></li>
				<li><a href="./adm_horarios">Administrar Horarios</a></li>
				<li><a href="./adm_solicitudes">Administrar Solicitudes</a></li>
				<li><a href="./adm_reportes">Descargar reporte general</a></li>
				<li><button class="navbar__action" id="CerrarS" type="button">Cerrar Sesión</button></li>
			</ul>
		</nav>
	</header>

	<main>
		<h1>Administrador</h1>
		<p>Resumen general del sistema de tutorías.</p>
		<p><a class="reporte-dashboard-descarga" href="./adm_reportes">Descargar reporte general (PDF)</a></p>

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
				const respuesta = await apiFetch("../api/admin/adm_estadisticas.php");
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
				const respuesta = await apiFetch("../api/admin/adm_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json(); 

				if (resultado.code === 200){
					window.location.href = "../index";
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