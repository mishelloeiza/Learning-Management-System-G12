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
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
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
		<button type="button" name="btnE" id="btnE">Editar Datos</button>
		<button type="submit" name="btnG" id="btnG" disabled >Guardar Cambios</button>
	</form>

	<h2>Cambiar contraseña</h2>
	<form id="formcam" method="post">
		<label for="contrasena_act">Contraseña Actual:</label>
		<input type="password" name="contrasena_act" placeholder="Ingrese su contraseña actual" required>
		<label for="contrasena_new">Nueva Contraseña:</label>
		<input type="password" name="contrasena_new" placeholder="Ingrese su nueva contraseña" required>
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

		Editar.addEventListener("click", function(){
			inputs.forEach(function(input) {
	            input.disabled = false;
	        });
	        Guardar.disabled = false;
        	Editar.disabled = true;
		});

		//boton de cerrar sesion
		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/estudiante/stu_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if(resultado.code = 200){
					window.location.href = "../prin_dashboard.php";
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
				const respuesta = await fetch("../api/estudiante/stu_perfil_cargar.php");

				const resultado = await respuesta.json();

				if(resultado.code = 200){
					document.getElementById("nombre").value = resultado.usuario.nombre;
					document.getElementById("apellido").value = resultado.usuario.apellido;
					document.getElementById("correo").value = resultado.usuario.correo;
					document.getElementById("telefono").value = resultado.usuario.telefono;
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
				const respuesta = await fetch("../api/estudiante/stu_perfil_actualizar.php", {
					method: "POST",
					body: formulario
				});

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					alert(resultado.message);
					cargardatos();
					inputs.forEach(function(input) {
						input.disabled = true;
					});
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

		document.getElementById("formcam").addEventListener("submit", async function(e) {
			e.preventDefault();
			const formulario = new FormData(this);
			try {
				const respuesta = await fetch("../api/estudiante/stu_perfil_camcont.php",{
					method: "POST",
					body: formulario
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

		document.getElementById("formeli").addEventListener("submit", async function(e) {
			e.preventDefault;
			try {
				const respuesta = await fetch("../api/estudiante/stu_perfil_eliminar.php",{
					method: "POST"
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

</body>
</html>

