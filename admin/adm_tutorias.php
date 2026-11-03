<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Tutorias</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
    <link rel="stylesheet" href="../CSS/navbar.css">

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
				<li><a href="./adm_perfil">Mi perfil</a></li>
				<li><a href="./adm_dashboard">Inicio</a></li>
				<li><a href="./adm_carreras">Administrar Carreras</a></li>
				<li><a href="./adm_materias">Administrar Materias</a></li>
				<li><a href="./adm_tutorias">Administrar Tutorias</a></li>
				<li><a href="./adm_usuarios">Administrar Usuarios</a></li>
				<li><a href="./adm_horarios">Administrar Horarios</a></li>
				<li><a href="./adm_solicitudes">Administrar Solicitudes</a></li>
				<li><a href="./adm_reportes">Reportes</a></li>
				<li><button class="navbar__action" id="CerrarS" type="button">Cerrar Sesión</button></li>
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
            <label for="cupos">Cupos</label>
            <input type="number" id="cupos" name="cupos" min="1" max="5" value="1" required>
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
            <label>Horarios</label>
            <div id="listaHorarios"></div>
            <button type="button" id="btnAgregarHorario">Agregar horario</button>
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
                    <th>Cupos</th>
                    <th>Horarios</th>
                    <th>Tutor</th>
                    <th>Materia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        const campos = ["titulo", "descripcion", "cupos", "fecha_inicio", "fecha_fin", "id_tutor", "id_materia", "estado"];
        let idtutoriaselect = null;
        let editando = false;

        function esc(texto) {
            const div = document.createElement("div");
            div.textContent = texto ?? "";
            return div.innerHTML;
        }

        const dias = ["lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo"];

        function agregarHorario(horario = {}) {
            const lista = document.getElementById("listaHorarios");
            if (lista.children.length >= 5) return;
            const fila = document.createElement("div");
            fila.dataset.idHorario = horario.id_horario || "";
            const dia = document.createElement("select");
            dia.required = true;
            dias.forEach(function(d) { dia.add(new Option(d, d)); });
            dia.value = horario.dia || "lunes";
            const hora = document.createElement("input");
            hora.type = "time";
            hora.required = true;
            hora.value = (horario.hora_inicio || "08:00").substring(0, 5);
            const quitar = document.createElement("button");
            quitar.type = "button";
            quitar.textContent = "Quitar";
            quitar.addEventListener("click", function() { fila.remove(); });
            fila.append(dia, hora, quitar);
            lista.appendChild(fila);
        }

        document.getElementById("btnAgregarHorario").addEventListener("click", function() { agregarHorario(); });

        function habilitarCampos(habilitar) {
            document.querySelectorAll("#listaHorarios select, #listaHorarios input, #listaHorarios button").forEach(function(campo) {
                campo.disabled = !habilitar;
            });
            document.getElementById("btnAgregarHorario").disabled = !habilitar;
            campos.forEach(function(id) {
                document.getElementById(id).disabled = !habilitar;
            });
        }

        function limpiarFormulario() {
            document.getElementById("formtut").reset();
            idtutoriaselect = null;
            editando = false;
            document.getElementById("listaHorarios").innerHTML = "";
            agregarHorario();
            habilitarCampos(true);
            document.getElementById("estado").disabled = true;
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Registrar";
        }

		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await apiFetch("../api/admin/adm_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					window.location.href = "../index";
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
                const respuesta = await apiFetch("../api/admin/cargar_contenidos/cargar_tutores.php");
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
                const respuesta = await apiFetch("../api/admin/cargar_contenidos/cargar_materias.php");
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
            const horarios = Array.from(document.querySelectorAll("#listaHorarios > div")).map(function(fila) {
                return {id_horario: fila.dataset.idHorario, dia: fila.querySelector("select").value, hora_inicio: fila.querySelector("input").value};
            });
            if (horarios.length < 1 || horarios.length < Number(document.getElementById("cupos").value)) {
                alert("Debe registrar suficientes horarios para los cupos seleccionados");
                return;
            }
            formulario.append("horarios", JSON.stringify(horarios));
            let url = "../api/admin/adm_tutorias/crear_tutoria.php";

            if (editando) {
                formulario.append("id_tutoria", idtutoriaselect);
                url = "../api/admin/adm_tutorias/editar_tutoria.php";
            }

            try {
                const respuesta = await apiFetch(url, {
                    method: url.includes("editar_") ? "PUT" : "POST",
                    body: new URLSearchParams(formulario)
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
                const respuesta = await apiFetch(url);
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message);
                    return;
                }

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (resultado.tutorias.length === 0) {
                    tabla.innerHTML = '<tr><td colspan="11">No se encontraron tutorias</td></tr>';
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
                        <td>${esc(tutoria.cupos)}</td>
                        <td>${(tutoria.horarios || []).map(h => esc(h.dia) + " " + esc(h.hora_inicio.substring(0, 5))).join(", ")}</td>
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
                        document.getElementById("cupos").value = tutoria.cupos;
                        document.getElementById("listaHorarios").innerHTML = "";
                        (tutoria.horarios || []).forEach(agregarHorario);
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
                            const respuesta_b = await apiFetch("../api/admin/adm_tutorias/eliminar_tutoria.php", {
                                method: "DELETE",
                                body: new URLSearchParams(dato)
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

        limpiarFormulario();
        cargarTutores();
        cargarMaterias();
        verTutorias();
    </script>

</body>
</html>
