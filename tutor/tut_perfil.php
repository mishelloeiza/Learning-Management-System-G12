<?php
	require_once("../api/tutor/tut_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Mi perfil</title>
</head>
<body>
	<header>
		<nav>
			<ul>
				<li>
					<a href="./tut_dashboard.php">Inicio</a>
				</li>
				<li>
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
			</ul>
		</nav>
	</header>

	<h1>Mi perfil</h1>

	<form id="formactu" method="post">
		<label for="nombre">Nombre:</label>
		<input type="text" id="nombre" name="nombre" disabled required>
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
		<button type="submit" name="btnG" id="btnG" disabled>Guardar Cambios</button>
	</form>

	<h2>Cambiar contraseña</h2>
	<form id="formcam" method="post">
		<label for="contrasena_act">Contraseña Actual:</label>
		<input type="password" id="contrasena_act" name="contrasena_act" placeholder="Ingrese su contraseña actual" required>
		<label for="contrasena_new">Nueva Contraseña:</label>
		<input type="password" id="contrasena_new" name="contrasena_new" placeholder="Ingrese su nueva contraseña" required>
		<button type="submit" name="btnC" id="btnC">Cambiar contraseña</button>
	</form>

	<form id="formeli" method="post">
		<button type="submit" name="btnElim">Eliminar cuenta</button>
	</form>

	<script>
		const Editar = document.getElementById("btnE");
		const Guardar = document.getElementById("btnG");
		const inputs = document.querySelectorAll("#formactu input");
		const combo = document.getElementById("carrera");

		function bloquearFormulario(bloquear) {
			inputs.forEach(function(input) {
				input.disabled = bloquear;
			});
			combo.disabled = bloquear;
			Guardar.disabled = bloquear;
			Editar.disabled = !bloquear;
		}

		Editar.addEventListener("click", function() {
			bloquearFormulario(false);
		});

		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/tutor/tut_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					window.location.href = "../prin_dashboard.php";
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});

		async function cargarCarreras() {
			try {
				const respuesta = await fetch("../api/tutor/tut_ver_carreras.php");
				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					resultado.carreras.forEach(function(carrera) {
						combo.appendChild(new Option(carrera.nombre, carrera.id_carrera));
					});
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al cargar carreras");
			}
		}

		async function cargarDatos() {
			try {
				const respuesta = await fetch("../api/tutor/tut_perfil_cargar.php");
				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					document.getElementById("nombre").value = resultado.usuario.nombre;
					document.getElementById("apellido").value = resultado.usuario.apellido;
					document.getElementById("correo").value = resultado.usuario.correo;
					document.getElementById("telefono").value = resultado.usuario.telefono;
					combo.value = resultado.usuario.idcarrera;
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al cargar datos");
			}
		}

		async function iniciar() {
			await cargarCarreras();
			await cargarDatos();
		}

		iniciar();

		document.getElementById("formactu").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await fetch("../api/tutor/tut_perfil_actualizar.php", {
					method: "POST",
					body: formulario
				});

				const resultado = await respuesta.json();
				alert(resultado.message);

				if (resultado.code === 200) {
					cargarDatos();
					bloquearFormulario(true);
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
			}
		});

		document.getElementById("formcam").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await fetch("../api/tutor/tut_perfil_camcont.php", {
					method: "POST",
					body: formulario
				});

				const resultado = await respuesta.json();
				alert(resultado.message);

				if (resultado.code === 200) {
					this.reset();
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
			}
		});

		document.getElementById("formeli").addEventListener("submit", async function(e) {
			e.preventDefault();

			if (!confirm("¿Estás seguro de que deseas eliminar tu cuenta?")) {
				return;
			}

			try {
				const respuesta = await fetch("../api/tutor/tut_perfil_eliminar.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();
				alert(resultado.message);

				if (resultado.code === 200) {
					window.location.href = "../prin_dashboard.php";
				}
			} catch (error) {
				console.log(error);
				alert("Ocurrio un error al comunicarse con el servidor");
			}
		});
	</script>

</body>
</html>
