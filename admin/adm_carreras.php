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
		<h1>Administracion carreras</h1>
	</main>
    <section>
        <h2>Registrar nueva carrera</h2>
        <form id="formcar" method="POST">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ingrese nombre de la carrera" required>
            <label for="descripcion">Descripcion</label>
            <input type="text" id="descripcion" name="descripcion" placeholder="Ingrese descripcion de la carrera" required>
            <label for="activo">Estado</label>
            <input type="checkbox" id="activo" name="activo" disabled>
            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de carreras</h2>
        <label for="buscar">Buscar carrera</label>
        <input type="text" id="buscar" name="buscar" placeholder="Nombre de carrera">
        <button type="button" id="btnBuscar">Buscar</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
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

                    formulario.append("id_carrera", idcarreraselect);
                    formulario.append("activo", estado);

                    const respuesta_e = await fetch("../api/admin/adm_carreras/editar_carrera.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado_e = await respuesta_e.json();

                    if(resultado_e.code === 200){
                        alert(resultado_e.message);
                        this.reset();
                        verCarreras();
                        idcarreraselect = null;
                        editando = false;
                        document.getElementById("activo").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }else {
                        alert(resultado_e.message);
                        idcarreraselect = null;
                        editando = false;
                        document.getElementById("nombre").value = "";
                        document.getElementById("descripcion").value = "";
                        document.getElementById("activo").checked = false;
                        document.getElementById("activo").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }
                }else{
                    const respuesta = await fetch("../api/admin/adm_carreras/crear_carrera.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado = await respuesta.json();

                    if(resultado.code === 200){
                        alert(resultado.message);
                        this.reset();
                        verCarreras();
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
        let idcarreraselect = null;
        let id_carreraelim = null;
        let editando = false;
        let eliminar = false;

        //Visualizar carreras
        async function verCarreras(busqueda = ""){
            try {
                const respuesta = await fetch("../api/admin/adm_carreras/ver_carreras.php?buscar=" + encodeURIComponent(busqueda));

                const resultado = await respuesta.json();

                if(resultado.code === 200){
                    const tabla = document.querySelector("table tbody");
                    tabla.innerHTML = "";
                    resultado.carreras.forEach(function(carrera) {
                        const fila = document.createElement("tr");
                        fila.innerHTML = `
                            <td>${carrera.id_carrera}</td>
                            <td>${carrera.nombre}</td>
                            <td>${carrera.descripcion}</td>
                            <td>${carrera.activo}</td>
                            <td>
                                <button type="button" class="btnSeleccionar">Seleccionar</button>
                                <button type="button" class="btnEliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta carrera?');">Eliminar</button>
                            </td>
                        `;
                        tabla.appendChild(fila);

                        //Seleccionar una fila
                        const seleccionar = fila.querySelector(".btnSeleccionar");

                        seleccionar.addEventListener("click", function(){
                            idcarreraselect = carrera.id_carrera;
                            document.getElementById("nombre").value = carrera.nombre;
                            document.getElementById("descripcion").value = carrera.descripcion;
                            document.getElementById("activo").checked = carrera.activo == 1;
                            editando = false;
                            document.getElementById("nombre").disabled = true;
                            document.getElementById("descripcion").disabled = true;
                            document.getElementById("activo").disabled = true;
                        });

                        //Eliminar una carreras
                        const eliminar = fila.querySelector(".btnEliminar");

                        eliminar.addEventListener("click", async function(){
                            id_carreraelim = carrera.id_carrera;
                            const dato = new FormData();
                            dato.append("id_carrera", id_carreraelim);
                            try {
                                const respuesta_b = await fetch("../api/admin/adm_carreras/eliminar_carrera.php", {
                                    method: "POST",
                                    body: dato
                                });

                                const resultado_b = await respuesta_b.json();

                                if(resultado_b.code === 200){
                                    alert(resultado_b.message);
                                    verCarreras();
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

        //Usar filtro
        document.getElementById("btnBuscar").addEventListener("click", function(){
            const buscar = document.getElementById("buscar").value.trim();
            verCarreras(buscar);
        });

        //Funciones del boton editar
        document.getElementById("btnEditar").addEventListener("click", function(){
            if(idcarreraselect === null){
                alert("Primero debe seleccionar una carrera");
                return;
            }
            document.getElementById("nombre").disabled = false;
            document.getElementById("descripcion").disabled = false;
            document.getElementById("activo").disabled = false;

            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            editando = true;
        });

        //Cancelar edicion
        document.getElementById("btnCancelar").addEventListener("click", async function(){
            idcarreraselect = null;
            editando = false;

            document.getElementById("nombre").value = "";
            document.getElementById("descripcion").value = "";
            document.getElementById("activo").checked = false;

            document.getElementById("nombre").disabled = false;
            document.getElementById("descripcion").disabled = false;
            document.getElementById("activo").disabled = true;

            document.getElementById("btnGuardar").textContent = "Registrar";
        });

        //Cargar funcion de ver carreras
        verCarreras();
    </script>
</body>
</html>