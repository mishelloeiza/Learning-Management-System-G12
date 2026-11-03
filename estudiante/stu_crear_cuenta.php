<?php require_once __DIR__ . '/../config/RedirectAuthenticated.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Crear Cuenta</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/password-visibility.css">
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
						<span class="password-visibility"><input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required autocomplete="new-password"><button type="button" class="password-visibility__toggle" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar u ocultar contraseña"><svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A11 11 0 0 1 12 6c6.4 0 10 6 10 6a16 16 0 0 1-3.2 3.7M6.3 6.4C3.5 8.2 2 12 2 12s3.6 6 10 6c1.8 0 3.4-.5 4.8-1.2M10 10a3 3 0 0 0 4 4"/></svg></button></span>
					</div>

					<button type="submit" name="btn" class="btn-primary">Crear cuenta</button>

					<p class="auth-switch">¿Ya tienes cuenta? <a href="../login">Inicia sesión</a></p>
				</form>
			</main>
		</div>
	</div>

	<script>
		//Funcion para cargar las carreras existentes
		async function Cargarcarreras(){
			try {
				const respuesta = await apiFetch("../api/estudiante/stu_ver_carreras.php");

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
				const respuesta = await apiFetch("../api/estudiante/stu_crear_cuenta.php", {
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


<script>
    // Cambia la visibilidad de la contraseña sin enviar ni modificar su valor.
    document.querySelectorAll('.password-visibility__toggle').forEach(function(boton) {
        boton.addEventListener('click', function() {
            const campo = boton.parentElement.querySelector('input');
            const visible = campo.type === 'password';
            campo.type = visible ? 'text' : 'password';
            boton.classList.toggle('is-visible', visible);
            boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            boton.setAttribute('aria-pressed', String(visible));
        });
    });
</script>
</body>

</html>
