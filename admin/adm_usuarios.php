<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Carreras</title>
</head>
<body>
    <header>
		<nav>
			<ul>
				<li>
					<a href="./adm_perfil.php">Mi perfil</a>
				</li>
				<li>
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
			</ul>
		</nav>
	</header>
	<main>
		<h1>Administracion usuarios</h1>
	</main>
    <section>
        <h2>Registrar nuevo usuario</h2>
        <form id="formcar" method="POST">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ingrese nombre del usuario" required>
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" placeholder="Ingrese apellido del usuario" required>
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" placeholder="Ingrese correo del usuario" required>
            <label for="telefono">Telefono</label>
            <input type="tel" id="telefono" name="telefono" placeholder="Ingrese telefono del usuario" required>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese contrasena del usuario">

            <label for="rol">Rol</label>
            <select name="rol" id="rol" required>
                <option value="">Seleccione un rol</option>
            </select>

            <label for="carrera">Carrera</label>
            <select name="carrera" id="carrera" required>
                <option value="">Seleccione una carrera</option>
            </select>


            <label for="activo">Estado</label>
            <input type="checkbox" id="activo" name="activo" disabled>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de usuarios</h2>

        <label for="buscar">Buscar email</label>
        <input type="text" id="buscar" name="buscar" placeholder="Email del usuario">

        <label for="filtroCarrera">Carrera</label>
        <select id="filtroCarrera" name="filtroCarrera">
            <option value="">Todas las carreras</option>
        </select>

        <label for="filtroRol">Rol</label>
        <select id="filtroRol" name="filtroRol">
            <option value="">Todos los roles</option>
        </select>

        <button type="button" id="btnBuscar">Buscar</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th>Rol</th>
                    <th>Carrera</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
        </table>
    </section>

    <script>
        //Funcion para cargar las carreras existentes
		async function Cargarcarreras(){
			try {
				const respuesta = await fetch("../api/admin/cargar_contenidos/cargar_carreras.php");

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					const selectCarrera = document.getElementById("carrera");
                    const selectFiltroC = document.getElementById("filtroCarrera");

					resultado.carreras.forEach(function(carrera) {
						const opcion = document.createElement('option');
						opcion.value = carrera.id_carrera;
						opcion.textContent = carrera.nombre;
						selectCarrera.appendChild(opcion);

                        const opcion2 = document.createElement('option');
                        opcion2.value = carrera.id_carrera;
                        opcion2.textContent = carrera.nombre;
                        selectFiltroC.appendChild(opcion2);
					});
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al comunicarse con el servidor");
			}
		}

        //Funcion para cargar las roles existentes
		async function Cargarroles(){
			try {
				const respuesta = await fetch("../api/admin/cargar_contenidos/cargar_roles.php");

				const resultado = await respuesta.json();

				if(resultado.code === 200){
					const selectrol = document.getElementById("rol");
                    const selectFiltroR = document.getElementById("filtroRol")

					resultado.roles.forEach(function(roles) {
						const opcion = document.createElement('option');
						opcion.value = roles.id_rol;
						opcion.textContent = roles.nombre;
						selectrol.appendChild(opcion);

                        const opcion2 = document.createElement('option');
                        opcion2.value = roles.id_rol;
                        opcion2.textContent = roles.nombre;
                        selectFiltroR.appendChild(opcion2);
					});
				}else {
					alert(resultado.message);
				}
			} catch (error) {
				console.log(error);
				alert("Error al comunicarse con el servidor");
			}
		}

        //Cerrar sesion
		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/admin/adm_cerrar_sesion.php", {
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

        //Crear y editar carrera
        document.getElementById("formcar").addEventListener("submit", async function(e){
            e.preventDefault();
            const formulario = new FormData(this);
            try {
                if(editando == true){
                    const estado = document.getElementById("activo").checked ? 1 : 0;

                    formulario.append("id_usuario", idusuarioselect);
                    formulario.append("activo", estado);

                    const respuesta_e = await fetch("../api/admin/adm_usuarios/editar_usuario.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado_e = await respuesta_e.json();

                    if(resultado_e.code === 200){
                        alert(resultado_e.message);
                        this.reset();
                        verUsuarios();
                        idusuarioselect = null;
                        editando = false;
                        document.getElementById("activo").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }else {
                        alert(resultado_e.message);
                        idusuarioselect = null;
                        editando = false;
                        this.reset();
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }
                }else{
                    const respuesta = await fetch("../api/admin/adm_usuarios/crear_usuario.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado = await respuesta.json();

                    if(resultado.code === 200){
                        alert(resultado.message);
                        this.reset();
                        verUsuarios();
                    }else {
                        alert(resultado.message);
                    }
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        //Variables para Editar y Eliminar datos
        let idusuarioselect = null;
        let id_usuarioelim = null;
        let editando = false;
        let eliminar = false;

        //Visualizar carreras
        async function verUsuarios(busqueda = "", carrera = "", rol = ""){
            const parametros = new URLSearchParams();

            parametros.append("buscar", busqueda);
            parametros.append("id_carrera", carrera);
            parametros.append("id_rol", rol);

            try {
                const respuesta = await fetch("../api/admin/adm_usuarios/ver_usuarios.php?" + parametros.toString());

                const resultado = await respuesta.json();

                if(resultado.code === 200){
                    const tabla = document.querySelector("table tbody");
                    tabla.innerHTML = "";
                    resultado.usuarios.forEach(function(usuarios) {
                        const fila = document.createElement("tr");
                        fila.innerHTML = `
                            <td>${usuarios.id_usuario}</td>
                            <td>${usuarios.nombre}</td>
                            <td>${usuarios.apellido}</td>
                            <td>${usuarios.correo}</td>
                            <td>${usuarios.telefono}</td>
                            <td>${usuarios.rol}</td>
                            <td>${usuarios.carrera}</td>
                            <td>${usuarios.activo}</td>
                            <td>
                                <button type="button" class="btnSeleccionar">Seleccionar</button>
                                <button type="button" class="btnEliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta carrera?');">Eliminar</button>
                            </td>
                        `;
                        tabla.appendChild(fila);

                        //Seleccionar una fila
                        const seleccionar = fila.querySelector(".btnSeleccionar");

                        seleccionar.addEventListener("click", function(){
                            idusuarioselect = usuarios.id_usuario;
                            document.getElementById("nombre").value = usuarios.nombre;
                            document.getElementById("apellido").value = usuarios.apellido;
                            document.getElementById("correo").value = usuarios.correo;
                            document.getElementById("telefono").value = usuarios.telefono;
                            document.getElementById("rol").value = usuarios.id_rol;
                            document.getElementById("carrera").value = usuarios.id_carrera;
                            document.getElementById("activo").checked = usuarios.activo == 1;
                            editando = false;
                            document.getElementById("nombre").disabled = true;
                            document.getElementById("apellido").disabled = true;
                            document.getElementById("correo").disabled = true;
                            document.getElementById("telefono").disabled = true;
                            document.getElementById("rol").disabled = true;
                            document.getElementById("carrera").disabled = true;
                            document.getElementById("activo").disabled = true;
                            document.getElementById("contrasena").disabled = true;
                            document.getElementById("btnGuardar").disabled = true;
                        });

                        //Eliminar una carreras
                        const eliminar = fila.querySelector(".btnEliminar");

                        eliminar.addEventListener("click", async function(){
                            id_usuarioelim = usuarios.id_usuario;
                            const dato = new FormData();
                            dato.append("id_usuario", id_usuarioelim);
                            try {
                                const respuesta_b = await fetch("../api/admin/adm_usuarios/eliminar_usuario.php", {
                                    method: "POST",
                                    body: dato
                                });

                                const resultado_b = await respuesta_b.json();

                                if(resultado_b.code === 200){
                                    alert(resultado_b.message);
                                    verUsuarios();
                                }else{
                                    alert(resultado_b.message);
                                }

                            } catch (error) {
                                console.log(error);
                                alert("Error al comunicarse con el servidor");
                            }
                        });
                    });
                }else {
                    alert(resultado.message);
                }
            } catch (error) {
                console.log(error);
                alert("Error al cargar carreras");
            }
        }

        //Usar filtros
        document.getElementById("btnBuscar").addEventListener("click", function(){
            const buscar = document.getElementById("buscar").value.trim();
            const carrera = document.getElementById("filtroCarrera").value;
            const rol = document.getElementById("filtroRol").value;
            verUsuarios(buscar, carrera, rol);
        });

        //Funciones del boton editar
        document.getElementById("btnEditar").addEventListener("click", function(){
            if(idusuarioselect === null){
                alert("Primero debe seleccionar una carrera");
                return;
            }
            document.getElementById("nombre").disabled = false;
            document.getElementById("apellido").disabled = false;
            document.getElementById("correo").disabled = false;
            document.getElementById("telefono").disabled = false;
            document.getElementById("rol").disabled = false;
            document.getElementById("carrera").disabled = false;
            document.getElementById("activo").disabled = false;
            document.getElementById("contrasena").disabled = false;

            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            document.getElementById("btnGuardar").disabled = false;
            editando = true;
        });

        //Cancelar edicion
        document.getElementById("btnCancelar").addEventListener("click", async function(){
            idusuarioselect = null;
            editando = false;

            document.getElementById("nombre").value = "";
            document.getElementById("apellido").value = "";
            document.getElementById("correo").value = "";
            document.getElementById("telefono").value = "";
            document.getElementById("rol").value = "";
            document.getElementById("carrera").value = "";
            document.getElementById("activo").checked = false;

            document.getElementById("nombre").disabled = false;
            document.getElementById("apellido").disabled = false;
            document.getElementById("correo").disabled = false;
            document.getElementById("telefono").disabled = false;
            document.getElementById("rol").disabled = false;
            document.getElementById("carrera").disabled = false;
            document.getElementById("activo").disabled = true;
            document.getElementById("contrasena").disabled = false;

            document.getElementById("btnGuardar").textContent = "Registrar";
            document.getElementById("btnGuardar").disabled = false;
        });

        //Cargar funcion de ver carreras
        verUsuarios();

        //Cargar opciones de los combobox
        Cargarcarreras();
        Cargarroles();
    </script>
</body>
</html>