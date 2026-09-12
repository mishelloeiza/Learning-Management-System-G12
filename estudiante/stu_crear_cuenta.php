<?php
	//si existe una sesion activa se cierra
	session_start();
	session_unset();
	session_destroy();
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Crear Cuenta</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
</head>

<body>
	<div class="auth-page">
		<div class="auth-card">
			<div class="auth-card-bar"></div>
			<main class="auth-form">
				<h2>Crear cuenta</h2>
				<p class="subtitle">Completá tus datos para registrarte.</p>

				<form action="./api/estudiante/crear_cuenta.php" id="form" method="post">
					<div class="field">
						<label for="nombre">Nombre</label>
						<input type="text" id="nombre" name="nombre" placeholder="Ingrese su nombre" required>
					</div>

					<div class="field">
						<label for="apellido">Apellido</label>
						<input type="text" id="apellido" name="apellido" placeholder="Ingrese su apellido" required>
					</div>

					<div class="field">
						<label for="correo">Correo</label>
						<input type="email" id="correo" name="correo" placeholder="Ingrese su correo" required>
					</div>

					<div class="field">
						<label for="telefono">Teléfono</label>
						<input type="tel" id="telefono" name="telefono" placeholder="Ingrese su teléfono" required>
					</div>

					<div class="field">
						<label for="carrera">Carrera</label>
						<select name="carrera" id="carrera" required>
							<option value="">Seleccione una carrera</option>
						</select>
					</div>

					<div class="field">
						<label for="contrasena">Contraseña</label>
						<input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
					</div>

					<button type="submit" name="btn" class="btn-primary">Crear cuenta</button>

					<p class="auth-switch">¿Ya tienes cuenta? <a href="./stu_login.php">Inicia sesión</a></p>
				</form>
			</main>
		</div>
	</div>

	<script>
		//Funcion para cargar las carreras existentes
		async function Cargarcarreras(){
			try {
				const respuesta = await fetch("../api/estudiante/stu_ver_carreras.php");

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					const selectCarrera = document.getElementById("carrera");

					resultado.carreras.forEach(function(carrera) {
						const opcion = document.createElement('option');
						opcion.value = carrera.id_carrera;
						opcion.textContent = carrera.nombre;
						selectCarrera.appendChild(opcion);
					});
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al comunicarse con el servidor");
			}
		}

		Cargarcarreras();

		document.getElementById("form").addEventListener("submit", async function(e) {
			//Evitar comportamiento normal
			e.preventDefault();
			//Recoger datos
			const formulario = new FormData(this);
			try {
				//Enviar datos a la API
				const respuesta = await fetch("../api/estudiante/stu_crear_cuenta.php", {
					method: "POST",
					body: formulario
				});
				//Guardar resultado de la API
				const resultado = await respuesta.json();
				//Si el resultado es ok
				if (resultado.code === 201) {
					alert(resultado.message);
					//Limpiar formulario
					this.reset();
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
