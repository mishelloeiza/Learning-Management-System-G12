<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Tutorias</title>
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
		<h1>Administracion tutorias</h1>
	</main>
    <section>
        <h2>Registrar nueva tutoria</h2>
        <form id="formtut" method="POST">
            <label for="titulo">Titulo</label>
            <input type="text" id="titulo" name="titulo" maxlength="255" placeholder="Ingrese titulo de la tutoria" required>
            <label for="descripcion">Descripcion</label>
            <input type="text" id="descripcion" name="descripcion" maxlength="255" placeholder="Ingrese descripcion de la tutoria" required>
            <label for="fecha_inicio">Fecha de inicio</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
            <label for="fecha_fin">Fecha de fin</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required>
            <label for="id_tutor">Tutor</label>
            <select name="id_tutor" id="id_tutor" required>
                <option value="">Seleccione un tutor</option>
            </select>
            <label for="id_materia">Materia</label>
            <select name="id_materia" id="id_materia" required>
                <option value="">Seleccione una materia</option>
            </select>
            <label for="estado">Estado</label>
            <select name="estado" id="estado" disabled>
                <option value="activa">Activa</option>
                <option value="en curso">En curso</option>
                <option value="finalizada">Finalizada</option>
                <option value="cancelada">Cancelada</option>
            </select>
            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de tutorias</h2>
        <label for="buscar">Buscar tutoria</label>
        <input type="text" id="buscar" name="buscar" placeholder="Titulo de la tutoria">
        <label for="filtroEstado">Estado</label>
        <select id="filtroEstado" name="filtroEstado">
            <option value="">Todos los estados</option>
            <option value="activa">Activa</option>
            <option value="en curso">En curso</option>
            <option value="finalizada">Finalizada</option>
            <option value="cancelada">Cancelada</option>
        </select>
        <label for="filtroMateria">Materia</label>
        <select id="filtroMateria" name="filtroMateria">
            <option value="">Todas las materias</option>
        </select>
        <button type="button" id="btnBuscar">Buscar</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>Descripcion</th>
                    <th>Estado</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Tutor</th>
                    <th>Materia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        const campos = ["titulo", "descripcion", "fecha_inicio", "fecha_fin", "id_tutor", "id_materia", "estado"];
        let idtutoriaselect = null;
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
            document.getElementById("formtut").reset();
            idtutoriaselect = null;
            editando = false;
            habilitarCampos(true);
            document.getElementById("estado").disabled = true;
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

        async function cargarTutores() {
            try {
                const respuesta = await fetch("../api/admin/cargar_contenidos/cargar_tutores.php");
                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    const selectTutor = document.getElementById("id_tutor");

                    resultado.tutores.forEach(function(tutor) {
                        const sufijo = tutor.activo == 1 ? "" : " (inactivo)";
                        selectTutor.appendChild(new Option(tutor.nombre + " " + tutor.apellido + sufijo, tutor.id_usuario));
                    });
                } else {
                    alert(resultado.message);
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        }

        async function cargarMaterias() {
            try {
                const respuesta = await fetch("../api/admin/cargar_contenidos/cargar_materias.php");
                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    const selectMateria = document.getElementById("id_materia");
                    const selectFiltro = document.getElementById("filtroMateria");

                    resultado.materias.forEach(function(materia) {
                        const sufijo = materia.activo == 1 ? "" : " (inactiva)";
                        selectMateria.appendChild(new Option(materia.nombre + sufijo, materia.id_materia));
                        selectFiltro.appendChild(new Option(materia.nombre + sufijo, materia.id_materia));
                    });
                } else {
                    alert(resultado.message);
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        }

        document.getElementById("formtut").addEventListener("submit", async function(e) {
            e.preventDefault();
            const formulario = new FormData(this);
            let url = "../api/admin/adm_tutorias/crear_tutoria.php";

            if (editando) {
                formulario.append("id_tutoria", idtutoriaselect);
                url = "../api/admin/adm_tutorias/editar_tutoria.php";
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
                    verTutorias();
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        async function verTutorias() {
            const buscar = document.getElementById("buscar").value.trim();
            const estado = document.getElementById("filtroEstado").value;
            const idMateria = document.getElementById("filtroMateria").value;

            try {
                const url = "../api/admin/adm_tutorias/ver_tutorias.php?buscar=" + encodeURIComponent(buscar) + "&estado=" + encodeURIComponent(estado) + "&id_materia=" + encodeURIComponent(idMateria);
                const respuesta = await fetch(url);
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message);
                    return;
                }

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (resultado.tutorias.length === 0) {
                    tabla.innerHTML = '<tr><td colspan="9">No se encontraron tutorias</td></tr>';
                    return;
                }

                resultado.tutorias.forEach(function(tutoria) {
                    const fila = document.createElement("tr");
                    fila.innerHTML = `
                        <td>${esc(tutoria.id_tutoria)}</td>
                        <td>${esc(tutoria.titulo)}</td>
                        <td>${esc(tutoria.descripcion)}</td>
                        <td>${esc(tutoria.estado)}</td>
                        <td>${esc(tutoria.fecha_inicio)}</td>
                        <td>${esc(tutoria.fecha_fin)}</td>
                        <td>${esc(tutoria.tutor)}</td>
                        <td>${esc(tutoria.materia)}</td>
                        <td>
                            <button type="button" class="btnSeleccionar">Seleccionar</button>
                            <button type="button" class="btnEliminar">Cancelar tutoria</button>
                        </td>
                    `;
                    tabla.appendChild(fila);

                    fila.querySelector(".btnSeleccionar").addEventListener("click", function() {
                        idtutoriaselect = tutoria.id_tutoria;
                        editando = false;
                        document.getElementById("titulo").value = tutoria.titulo;
                        document.getElementById("descripcion").value = tutoria.descripcion;
                        document.getElementById("fecha_inicio").value = tutoria.fecha_inicio;
                        document.getElementById("fecha_fin").value = tutoria.fecha_fin;
                        document.getElementById("id_tutor").value = tutoria.id_tutor;
                        document.getElementById("id_materia").value = tutoria.id_materia;
                        document.getElementById("estado").value = tutoria.estado;
                        habilitarCampos(false);
                        document.getElementById("btnGuardar").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    });

                    fila.querySelector(".btnEliminar").addEventListener("click", async function() {
                        if (!confirm("¿Estás seguro de que deseas cancelar esta tutoria?")) {
                            return;
                        }

                        const dato = new FormData();
                        dato.append("id_tutoria", tutoria.id_tutoria);

                        try {
                            const respuesta_b = await fetch("../api/admin/adm_tutorias/eliminar_tutoria.php", {
                                method: "POST",
                                body: dato
                            });

                            const resultado_b = await respuesta_b.json();
                            alert(resultado_b.message);

                            if (resultado_b.code === 200) {
                                if (idtutoriaselect === tutoria.id_tutoria) {
                                    limpiarFormulario();
                                }
                                verTutorias();
                            }
                        } catch (error) {
                            console.log(error);
                            alert("Error al comunicarse con el servidor");
                        }
                    });
                });
            } catch (error) {
                console.log(error);
                alert("Error al cargar tutorias");
            }
        }

        document.getElementById("btnBuscar").addEventListener("click", verTutorias);

        document.getElementById("btnEditar").addEventListener("click", function() {
            if (idtutoriaselect === null) {
                alert("Primero debe seleccionar una tutoria");
                return;
            }
            habilitarCampos(true);
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            editando = true;
        });

        document.getElementById("btnCancelar").addEventListener("click", limpiarFormulario);

        cargarTutores();
        cargarMaterias();
        verTutorias();
    </script>
</body>
</html>
