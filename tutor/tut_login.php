<?php
	session_start();
	session_unset();
	session_destroy();
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login Tutores</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
</head>

<body>
	<div class="auth-page">
		<div class="auth-card">
			<div class="auth-card-bar"></div>
			<main class="auth-form">
				<h2>Iniciar sesión Tutores</h2>
				<p class="subtitle">Ingresá con tu correo institucional.</p>

				<form id="form" method="post">
					<div class="field">
						<label for="usuario">Correo</label>
						<input type="text" id="usuario" name="usuario" placeholder="Ingrese su correo">
					</div>

					<div class="field">
						<label for="contrasena">Contraseña</label>
						<input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña">
					</div>

					<button type="submit" name="btn" class="btn-primary">Ingresar</button>
				</form>
			</main>
		</div>
	</div>

	<script>
		document.getElementById("form").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await fetch("../api/tutor/tut_login.php", {
					method: "POST",
					body: formulario
				});
				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					alert(resultado.message);
					this.reset();
					window.location.href = "./tut_dashboard.php";
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrió un error al comunicarse con el servidor.");
			}
		});
	</script>

</body>

</html>
