<?php
	require_once("../api/estudiante/stu_verificar_sesion.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Stu Perfil | Campus Virtual G12</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
	<link rel="stylesheet" href="../CSS/navbar.css">

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

	<header class="site-header">
		<nav class="navbar" aria-label="Navegación principal">
            <span class="navbar__brand" aria-label="LMS G12"><span class="navbar__brand-mark" aria-hidden="true">G12</span><span class="navbar__brand-name">Campus<span>Virtual</span></span></span>
			<input class="navbar__check" id="navToggle" type="checkbox" aria-label="Mostrar menú de navegación">
			<label class="navbar__toggle" for="navToggle" aria-label="Abrir o cerrar menú">
			<span class="navbar__bars" aria-hidden="true"></span>
			</label>
			<ul class="navbar__menu" id="menuPrincipal">
				<li><a href="./stu_reportes">Reportes</a></li>
				<li><button class="navbar__action" id="CerrarS" type="button">Cerrar Sesión</button></li>
			</ul>
		</nav>
	</header>

	<h1>Mi perfil</h1>

	<form id="formactu" method="post">
		<label for="nombre">Nombre:</label>
		<input type="text" id="nombre" name="nombre"  disabled required>
		<label for="apellido">Apellido:</label>
		<input type="text" id="apellido" name="apellido" disabled required>
		<label for="correo">Correo</label>
		<input type="email" id="correo" name="correo" disabled required>
		<label for="telefono">Teléfono</label>
		<input type="tel" id="telefono" name="telefono" disabled required>

    	<label for="carrera">Carrera</label>
    	<select id="carrera" name="carrera" disabled required>
        	<option value="">Seleccione una carrera</option>
    	</select>

		<button type="button" name="btnE" id="btnE">Editar Datos</button>
		<button type="submit" name="btnG" id="btnG" disabled >Guardar Cambios</button>
	</form>

	<h2>Cambiar contraseña</h2>
	<form id="formcam" method="post">
		<label for="contrasena_act">Contraseña Actual:</label>
		<span class="password-visibility"><input type="password" name="contrasena_act" placeholder="Ingrese su contraseña actual" required autocomplete="current-password"><button type="button" class="password-visibility__toggle" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar u ocultar contraseña"><svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A11 11 0 0 1 12 6c6.4 0 10 6 10 6a16 16 0 0 1-3.2 3.7M6.3 6.4C3.5 8.2 2 12 2 12s3.6 6 10 6c1.8 0 3.4-.5 4.8-1.2M10 10a3 3 0 0 0 4 4"/></svg></button></span>
		<label for="contrasena_new">Nueva Contraseña:</label>
		<span class="password-visibility"><input type="password" name="contrasena_new" placeholder="Ingrese su nueva contraseña" required autocomplete="new-password"><button type="button" class="password-visibility__toggle" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar u ocultar contraseña"><svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A11 11 0 0 1 12 6c6.4 0 10 6 10 6a16 16 0 0 1-3.2 3.7M6.3 6.4C3.5 8.2 2 12 2 12s3.6 6 10 6c1.8 0 3.4-.5 4.8-1.2M10 10a3 3 0 0 0 4 4"/></svg></button></span>
		<button type="submit" name="btnC" id="btnC">Cambiar contraseña</button>
	</form>

	<form id="formeli" method="post"
	onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu cuenta?');">
	    <button type="submit" name="btnElim">
	        Eliminar cuenta
	    </button>
	</form>

	<script>
		//Habilitar boton de guardar
		const Editar = document.getElementById("btnE");
		const Guardar = document.getElementById("btnG");
		const inputs = document.querySelectorAll("input");
		const combo = document.getElementById("carrera");

		Editar.addEventListener("click", function(){
			inputs.forEach(function(input) {
	            input.disabled = false;
	        });
			combo.disabled = false;
	        Guardar.disabled = false;
        	Editar.disabled = true;
		});

		//boton de cerrar sesion
		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await apiFetch("../api/estudiante/stu_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if(resultado.code = 200){
					window.location.href = "../index";
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});

		//cargar datos
		async function cargardatos() {
			try {
				//cargar la api
				const respuesta = await apiFetch("../api/estudiante/stu_perfil_cargar.php");
				const resultado = await respuesta.json();

				const respuesta2 = await apiFetch("../api/estudiante/stu_ver_carreras.php");
				const resultado2 = await respuesta2.json();

				if(resultado.code == 200 && resultado2.code == 200){
					document.getElementById("nombre").value = resultado.usuario.nombre;
					document.getElementById("apellido").value = resultado.usuario.apellido;
					document.getElementById("correo").value = resultado.usuario.correo;
					document.getElementById("telefono").value = resultado.usuario.telefono;

					const selectCarrera = document.getElementById("carrera");
					resultado2.carreras.forEach(function(carrera){
						const opcion = document.createElement("option");
						opcion.value = carrera.id_carrera;
						opcion.textContent = carrera.nombre;

						selectCarrera.appendChild(opcion);
					});

					selectCarrera.value = resultado.usuario.idcarrera;

				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al cargar datos");
			}
		}

		cargardatos();

		//Actualizar datos
		document.getElementById("formactu").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await apiFetch("../api/estudiante/stu_perfil_actualizar.php", {
					method: "PUT",
					body: new URLSearchParams(formulario)
				});

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					alert(resultado.message);
					cargardatos();
					inputs.forEach(function(input) {
						input.disabled = true;
					});
					combo.disabled = true;
					Guardar.disabled = true;
        			Editar.disabled = false;
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
			}
		});

		//Cambiar contraseña
		document.getElementById("formcam").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await apiFetch("../api/estudiante/stu_perfil_camcont.php",{
					method: "PUT",
					body: new URLSearchParams(formulario)
				});

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					alert(resultado.message);
					this.reset();
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
			}
		});

		//Eliminar cuenta
		document.getElementById("formeli").addEventListener("submit", async function(e) {
			e.preventDefault;
			try {
				const respuesta = await apiFetch("../api/estudiante/stu_perfil_eliminar.php",{
					method: "DELETE"
				});

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					alert(resultado.message);
				}else{
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
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

