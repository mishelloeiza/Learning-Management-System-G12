<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Materias</title>
</head>
<body>
    <header>
		<nav>
			<ul>
				<li>
					<a href="./adm_dashboard.php">Inicio</a>
				</li>
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
		<h1>Administracion materias</h1>
	</main>
    <section>
        <h2>Registrar nueva materia</h2>
        <form id="formmat" method="POST">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="255" placeholder="Ingrese nombre de la materia" required>
            <label for="descripcion">Descripcion</label>
            <input type="text" id="descripcion" name="descripcion" maxlength="255" placeholder="Ingrese descripcion de la materia" required>
            <label for="id_carrera">Carrera</label>
            <select name="id_carrera" id="id_carrera" required>
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
        <h2>Lista de materias</h2>
        <label for="buscar">Buscar materia</label>
        <input type="text" id="buscar" name="buscar" placeholder="Nombre de la materia">
        <label for="filtroCarrera">Carrera</label>
        <select id="filtroCarrera" name="filtroCarrera">
            <option value="">Todas las carreras</option>
        </select>
        <button type="button" id="btnBuscar">Buscar</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th>Carrera</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        const campos = ["nombre", "descripcion", "id_carrera"];
        let idmateriaselect = null;
        let editando = false;

        function esc(texto) {
            const div = document.createElement("div");
            div.textContent = texto ?? "";
            return div.innerHTML;
        }

        function habilitarCampos(habilitar) {
            campos.forEach(function(id) {
                document.getElementById(id).disabled = !habilitar;
            });
        }

        function limpiarFormulario() {
            document.getElementById("formmat").reset();
            idmateriaselect = null;
            editando = false;
            habilitarCampos(true);
            document.getElementById("activo").disabled = true;
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Registrar";
        }

		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/admin/adm_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					window.location.href = "../prin_dashboard.php";
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});

        async function cargarCarreras() {
            try {
                const respuesta = await fetch("../api/admin/cargar_contenidos/cargar_carreras.php");
                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    const selectCarrera = document.getElementById("id_carrera");
                    const selectFiltro = document.getElementById("filtroCarrera");

                    resultado.carreras.forEach(function(carrera) {
                        selectCarrera.appendChild(new Option(carrera.nombre, carrera.id_carrera));
                        selectFiltro.appendChild(new Option(carrera.nombre, carrera.id_carrera));
                    });
                } else {
                    alert(resultado.message);
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        }

        document.getElementById("formmat").addEventListener("submit", async function(e) {
            e.preventDefault();
            const formulario = new FormData(this);
            let url = "../api/admin/adm_materias/crear_materia.php";

            if (editando) {
                formulario.append("id_materia", idmateriaselect);
                formulario.append("activo", document.getElementById("activo").checked ? 1 : 0);
                url = "../api/admin/adm_materias/editar_materia.php";
            }

            try {
                const respuesta = await fetch(url, {
                    method: "POST",
                    body: formulario
                });

                const resultado = await respuesta.json();
                alert(resultado.message);

                if (resultado.code === 200) {
                    limpiarFormulario();
                    verMaterias();
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        async function verMaterias() {
            const buscar = document.getElementById("buscar").value.trim();
            const idCarrera = document.getElementById("filtroCarrera").value;

            try {
                const url = "../api/admin/adm_materias/ver_materias.php?buscar=" + encodeURIComponent(buscar) + "&id_carrera=" + encodeURIComponent(idCarrera);
                const respuesta = await fetch(url);
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message);
                    return;
                }

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (resultado.materias.length === 0) {
                    tabla.innerHTML = '<tr><td colspan="6">No se encontraron materias</td></tr>';
                    return;
                }

                resultado.materias.forEach(function(materia) {
                    const fila = document.createElement("tr");
                    fila.innerHTML = `
                        <td>${esc(materia.id_materia)}</td>
                        <td>${esc(materia.nombre)}</td>
                        <td>${esc(materia.descripcion)}</td>
                        <td>${esc(materia.carrera)}</td>
                        <td>${materia.activo == 1 ? "Activa" : "Inactiva"}</td>
                        <td>
                            <button type="button" class="btnSeleccionar">Seleccionar</button>
                            <button type="button" class="btnEliminar">Eliminar</button>
                        </td>
                    `;
                    tabla.appendChild(fila);

                    fila.querySelector(".btnSeleccionar").addEventListener("click", function() {
                        idmateriaselect = materia.id_materia;
                        editando = false;
                        document.getElementById("nombre").value = materia.nombre;
                        document.getElementById("descripcion").value = materia.descripcion;
                        document.getElementById("id_carrera").value = materia.id_carrera;
                        document.getElementById("activo").checked = materia.activo == 1;
                        habilitarCampos(false);
                        document.getElementById("activo").disabled = true;
                        document.getElementById("btnGuardar").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    });

                    fila.querySelector(".btnEliminar").addEventListener("click", async function() {
                        if (!confirm("¿Estás seguro de que deseas eliminar esta materia?")) {
                            return;
                        }

                        const dato = new FormData();
                        dato.append("id_materia", materia.id_materia);

                        try {
                            const respuesta_b = await fetch("../api/admin/adm_materias/eliminar_materia.php", {
                                method: "POST",
                                body: dato
                            });

                            const resultado_b = await respuesta_b.json();
                            alert(resultado_b.message);

                            if (resultado_b.code === 200) {
                                if (idmateriaselect === materia.id_materia) {
                                    limpiarFormulario();
                                }
                                verMaterias();
                            }
                        } catch (error) {
                            console.log(error);
                            alert("Error al comunicarse con el servidor");
                        }
                    });
                });
            } catch (error) {
                console.log(error);
                alert("Error al cargar materias");
            }
        }

        document.getElementById("btnBuscar").addEventListener("click", verMaterias);

        document.getElementById("btnEditar").addEventListener("click", function() {
            if (idmateriaselect === null) {
                alert("Primero debe seleccionar una materia");
                return;
            }
            habilitarCampos(true);
            document.getElementById("activo").disabled = false;
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            editando = true;
        });

        document.getElementById("btnCancelar").addEventListener("click", limpiarFormulario);

        cargarCarreras();
        verMaterias();
    </script>
</body>
</html>
