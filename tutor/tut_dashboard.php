<?php
	require_once("../api/tutor/tut_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tutor</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
	<link rel="stylesheet" href="../CSS/tarjetas.css">
	<link rel="stylesheet" href="../CSS/navbar.css">
</head>
<body>
	<header>
		<nav>
			<ul>
				<li>
					<a href="./tut_perfil.php">Mi perfil</a>
				</li>
				<li>
					<button id="CerrarS">Cerrar Sesión</button>
				</li>
			</ul>
		</nav>
	</header>
	<main>
		<h1>Tutor</h1>

		<section class="resumen-dashboard" aria-live="polite">

			<article class="tarjeta-resumen">
				<h3>Mis tutorías</h3>
				<p class="resumen-cantidad" data-stat="total">–</p>
				<small data-desc="total">Tutorías creadas por ti</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Activas</h3>
				<p class="resumen-cantidad" data-stat="activas">–</p>
				<small data-desc="activas">Publicadas, aún sin iniciar</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>En curso</h3>
				<p class="resumen-cantidad" data-stat="en_curso">–</p>
				<small data-desc="en_curso">Tutorías en desarrollo</small>
				<button type="button" class="abrirEnCurso">Ver en curso</button>
			</article>

			<article class="tarjeta-resumen">
				<h3>Finalizadas</h3>
				<p class="resumen-cantidad" data-stat="finalizadas">–</p>
				<small data-desc="finalizadas">Tutorías ya concluidas</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes pendientes</h3>
				<p class="resumen-cantidad" data-stat="solicitudes_pendientes">–</p>
				<small data-desc="solicitudes_pendientes">Esperando tu respuesta</small>
				<button type="button" class="abrirSolicitudes">Ver solicitudes</button>
			</article>

			<article class="tarjeta-resumen">
				<h3>Estudiantes</h3>
				<p class="resumen-cantidad" data-stat="estudiantes">–</p>
				<small data-desc="estudiantes">Estudiantes con solicitud aprobada</small>
			</article>

		</section>

		<section>
			<h2>Mis tutorías</h2>
			<button type="button" id="btnNueva">Nueva tutoría</button>
			<button type="button" id="btnSolicitudes">Ver solicitudes</button>
			<button type="button" id="btnEnCurso">Tutorías en curso</button>

			<div>
				<label for="fFechaInicio">Fecha de inicio (desde)</label>
				<input type="date" id="fFechaInicio">
				<label for="fFechaFin">Fecha de fin (hasta)</label>
				<input type="date" id="fFechaFin">
				<label for="fMateria">Materia</label>
				<select id="fMateria">
					<option value="">Todas las materias</option>
				</select>
				<label for="fEstado">Estado</label>
				<select id="fEstado">
					<option value="">Todos los estados</option>
					<option value="activa">Activa</option>
					<option value="en curso">En curso</option>
					<option value="finalizada">Finalizada</option>
					<option value="cancelada">Cancelada</option>
				</select>
				<button type="button" id="btnFiltrar">Filtrar</button>
				<button type="button" id="btnLimpiar">Limpiar</button>
			</div>

			<table>
				<thead>
					<tr>
						<th>Título</th>
						<th>Materia</th>
						<th>Descripción</th>
						<th>Fechas</th>
						<th>Horarios</th>
						<th>Cupos</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody id="tbTutorias"></tbody>
			</table>
		</section>
	</main>

	<dialog id="modalTutoria">
		<h2 id="tituloModalTutoria">Nueva tutoría</h2>
		<form id="formTutoria">
			<label for="titulo">Título</label>
			<input type="text" id="titulo" name="titulo" maxlength="255" required>
			<label for="descripcion">Descripción</label>
			<input type="text" id="descripcion" name="descripcion" maxlength="255" required>
			<label for="id_materia">Materia (solo sus especialidades)</label>
			<select id="id_materia" name="id_materia" required>
				<option value="">Seleccione una materia</option>
			</select>
			<label for="cupos">Cupos</label>
			<input type="number" id="cupos" name="cupos" min="1" max="5" value="1" required>
			<label for="fecha_inicio">Fecha de inicio</label>
			<input type="date" id="fecha_inicio" name="fecha_inicio" required>
			<label for="fecha_fin">Fecha de fin</label>
			<input type="date" id="fecha_fin" name="fecha_fin" required>

			<fieldset>
				<legend>Horarios (máx. 5, cada uno dura 1 hora)</legend>
				<div id="listaHorarios"></div>
				<button type="button" id="btnAgregarHorario">Agregar horario</button>
			</fieldset>

			<button type="submit" id="btnGuardar">Guardar</button>
			<button type="button" class="cerrarModal" data-modal="modalTutoria">Cancelar</button>
		</form>
	</dialog>

	<dialog id="modalSolicitudes">
		<h2>Solicitudes de estudiantes</h2>
		<div>
			<label for="sFechaInicio">Fecha de inicio</label>
			<input type="date" id="sFechaInicio">
			<label for="sFechaFin">Fecha de fin</label>
			<input type="date" id="sFechaFin">
			<label for="sMateria">Materia</label>
			<select id="sMateria">
				<option value="">Todas las materias</option>
			</select>
			<label for="sEstado">Estado</label>
			<select id="sEstado">
				<option value="">Todos</option>
				<option value="pendiente">Pendiente</option>
				<option value="aprobada">Aprobada</option>
				<option value="rechazada">Rechazada</option>
			</select>
			<button type="button" id="btnFiltrarSol">Filtrar</button>
		</div>
		<table>
			<thead>
				<tr>
					<th>Estudiante</th>
					<th>Tutoría</th>
					<th>Materia</th>
					<th>Horario</th>
					<th>Solicitada</th>
					<th>Cupos</th>
					<th>Estado</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody id="tbSolicitudes"></tbody>
		</table>
		<button type="button" class="cerrarModal" data-modal="modalSolicitudes">Cerrar</button>
	</dialog>

	<dialog id="modalEnCurso">
		<h2>Tutorías en curso</h2>
		<div>
			<label for="eFechaInicio">Fecha de inicio</label>
			<input type="date" id="eFechaInicio">
			<label for="eFechaFin">Fecha de fin</label>
			<input type="date" id="eFechaFin">
			<label for="eMateria">Materia</label>
			<select id="eMateria">
				<option value="">Todas las materias</option>
			</select>
			<button type="button" id="btnFiltrarEnCurso">Filtrar</button>
		</div>
		<div id="listaEnCurso"></div>
		<button type="button" class="cerrarModal" data-modal="modalEnCurso">Cerrar</button>
	</dialog>

	<script>
		const API = "../api/tutor/tutorias/";
		const DIAS = ["lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo"];
		const MAX_HORARIOS = 5;
		let idTutoriaEditar = null;

		function esc(texto) {
			const div = document.createElement("div");
			div.textContent = texto ?? "";
			return div.innerHTML;
		}

		function hora(h) {
			return String(h).substring(0, 5);
		}

		function qs(params) {
			return new URLSearchParams(params).toString();
		}

		async function pedir(url, opciones) {
			try {
				const respuesta = await fetch(url, opciones);
				return await respuesta.json();
			} catch (error) {
				console.error(error);
				return { code: -1, message: "Error al comunicarse con el servidor" };
			}
		}

		document.querySelectorAll(".cerrarModal").forEach(function(btn) {
			btn.addEventListener("click", function() {
				document.getElementById(btn.dataset.modal).close();
			});
		});

		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/tutor/tut_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if (resultado.code === 200) {
					window.location.href = "../index.php";
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});

		async function cargarEstadisticas() {
			const r = await pedir("../api/tutor/tut_estadisticas.php");
			if (r.code !== 200) {
				console.error(r.message);
				return;
			}
			document.querySelectorAll("[data-stat]").forEach(function(el) {
				el.textContent = r.estadisticas[el.dataset.stat] ?? "–";
			});
		}

		document.querySelectorAll(".abrirEnCurso").forEach(function(btn) {
			btn.addEventListener("click", function() { document.getElementById("btnEnCurso").click(); });
		});
		document.querySelectorAll(".abrirSolicitudes").forEach(function(btn) {
			btn.addEventListener("click", function() { document.getElementById("btnSolicitudes").click(); });
		});

		async function cargarMaterias() {
			const r = await pedir(API + "mis_materias.php");
			if (r.code !== 200) {
				alert(r.message);
				return;
			}
			["id_materia", "fMateria", "sMateria", "eMateria"].forEach(function(id) {
				const select = document.getElementById(id);
				r.materias.forEach(function(m) {
					select.appendChild(new Option(m.nombre, m.id_materia));
				});
			});
			if (r.materias.length === 0) {
				alert("Aún no tiene materias de especialidad asignadas, no podrá crear tutorías.");
			}
		}

		function textoHorarios(t) {
			return t.horarios.map(function(h) {
				let marca = "";
				if (h.estado === "asignado") {
					marca = " ✔ " + esc(h.estudiante);
				} else if (h.estado_solicitud === "pendiente") {
					marca = " (reservado: pendiente)";
				}
				return esc(h.dia) + " " + hora(h.hora_inicio) + "-" + hora(h.hora_fin) + marca;
			}).join("<br>");
		}

		async function verTutorias() {
			const r = await pedir(API + "ver_tutorias.php?" + qs({
				fecha_inicio: document.getElementById("fFechaInicio").value,
				fecha_fin: document.getElementById("fFechaFin").value,
				id_materia: document.getElementById("fMateria").value,
				estado: document.getElementById("fEstado").value
			}));

			const tabla = document.getElementById("tbTutorias");
			tabla.innerHTML = "";

			if (r.code !== 200) {
				alert(r.message);
				return;
			}
			if (r.tutorias.length === 0) {
				tabla.innerHTML = '<tr><td colspan="8">No se encontraron tutorías</td></tr>';
				return;
			}

			r.tutorias.forEach(function(t) {
				const vigente = t.estado === "activa" || t.estado === "en curso";
				const fila = document.createElement("tr");
				fila.innerHTML = `
					<td>${esc(t.titulo)}</td>
					<td>${esc(t.materia)}</td>
					<td>${esc(t.descripcion)}</td>
					<td>${esc(t.fecha_inicio)} a ${esc(t.fecha_fin)}</td>
					<td>${textoHorarios(t)}</td>
					<td>${esc(t.aprobadas)} / ${esc(t.cupos)}${t.abierta ? "" : " (cerrada)"}${t.pendientes > 0 ? "<br>" + esc(t.pendientes) + " pendiente(s)" : ""}</td>
					<td>${esc(t.estado)}</td>
					<td>
						<button type="button" class="btnEditar" ${vigente ? "" : "disabled"}>Modificar</button>
						<button type="button" class="btnEliminar" ${vigente ? "" : "disabled"}>Eliminar</button>
					</td>
				`;
				tabla.appendChild(fila);

				fila.querySelector(".btnEditar").addEventListener("click", function() {
					abrirFormulario(t);
				});

				fila.querySelector(".btnEliminar").addEventListener("click", async function() {
					if (!confirm("¿Eliminar esta tutoría? Se cancelarán sus horarios y las solicitudes pendientes se rechazarán.")) {
						return;
					}
					const datos = new FormData();
					datos.append("id_tutoria", t.id_tutoria);
					const res = await pedir(API + "eliminar_tutoria.php", { method: "POST", body: datos });
					alert(res.message);
					if (res.code === 200) {
						verTutorias();
						cargarEstadisticas();
					}
				});
			});
		}

		document.getElementById("btnFiltrar").addEventListener("click", verTutorias);
		document.getElementById("btnLimpiar").addEventListener("click", function() {
			["fFechaInicio", "fFechaFin", "fMateria", "fEstado"].forEach(function(id) {
				document.getElementById(id).value = "";
			});
			verTutorias();
		});

		function agregarFilaHorario(h) {
			const lista = document.getElementById("listaHorarios");
			if (lista.children.length >= MAX_HORARIOS) {
				alert("Máximo " + MAX_HORARIOS + " horarios por tutoría");
				return;
			}
			const fila = document.createElement("div");
			fila.dataset.id = h && h.id_horario ? h.id_horario : "";
			fila.innerHTML = `
				<select class="hDia" required>
					${DIAS.map(function(d) { return `<option value="${d}">${d}</option>`; }).join("")}
				</select>
				<input type="time" class="hInicio" required>
				<span>(1 hora)</span>
				<button type="button" class="quitarHorario">Quitar</button>
			`;
			if (h) {
				fila.querySelector(".hDia").value = h.dia;
				fila.querySelector(".hInicio").value = hora(h.hora_inicio);
			}
			fila.querySelector(".quitarHorario").addEventListener("click", function() {
				fila.remove();
			});
			lista.appendChild(fila);
		}

		function abrirFormulario(t) {
			const form = document.getElementById("formTutoria");
			form.reset();
			document.getElementById("listaHorarios").innerHTML = "";
			idTutoriaEditar = t ? t.id_tutoria : null;
			document.getElementById("tituloModalTutoria").textContent = t ? "Modificar tutoría" : "Nueva tutoría";

			if (t) {
				document.getElementById("titulo").value = t.titulo;
				document.getElementById("descripcion").value = t.descripcion;
				document.getElementById("id_materia").value = t.id_materia;
				document.getElementById("cupos").value = t.cupos;
				document.getElementById("fecha_inicio").value = t.fecha_inicio;
				document.getElementById("fecha_fin").value = t.fecha_fin;
				t.horarios.forEach(agregarFilaHorario);
			} else {
				agregarFilaHorario(null);
			}
			document.getElementById("modalTutoria").showModal();
		}

		document.getElementById("btnNueva").addEventListener("click", function() {
			abrirFormulario(null);
		});
		document.getElementById("btnAgregarHorario").addEventListener("click", function() {
			agregarFilaHorario(null);
		});

		document.getElementById("formTutoria").addEventListener("submit", async function(e) {
			e.preventDefault();

			const horarios = Array.from(document.querySelectorAll("#listaHorarios > div")).map(function(fila) {
				return {
					id_horario: fila.dataset.id || null,
					dia: fila.querySelector(".hDia").value,
					hora_inicio: fila.querySelector(".hInicio").value
				};
			});
			if (horarios.length === 0) {
				alert("Agregue al menos un horario");
				return;
			}

			const datos = new FormData(this);
			datos.append("horarios", JSON.stringify(horarios));
			let url = API + "crear_tutoria.php";
			if (idTutoriaEditar !== null) {
				datos.append("id_tutoria", idTutoriaEditar);
				url = API + "editar_tutoria.php";
			}

			const r = await pedir(url, { method: "POST", body: datos });
			alert(r.message);
			if (r.code === 200) {
				document.getElementById("modalTutoria").close();
				verTutorias();
				cargarEstadisticas();
			}
		});

		async function verSolicitudes() {
			const r = await pedir(API + "ver_solicitudes.php?" + qs({
				fecha_inicio: document.getElementById("sFechaInicio").value,
				fecha_fin: document.getElementById("sFechaFin").value,
				id_materia: document.getElementById("sMateria").value,
				estado: document.getElementById("sEstado").value
			}));

			const tabla = document.getElementById("tbSolicitudes");
			tabla.innerHTML = "";

			if (r.code !== 200) {
				alert(r.message);
				return;
			}
			if (r.solicitudes.length === 0) {
				tabla.innerHTML = '<tr><td colspan="8">No se encontraron solicitudes</td></tr>';
				return;
			}

			r.solicitudes.forEach(function(s) {
				const pendiente = s.estado === "pendiente";
				const lleno = Number(s.aprobadas) >= Number(s.cupos);
				const fila = document.createElement("tr");
				fila.innerHTML = `
					<td>${esc(s.estudiante)}<br>${esc(s.correo)}</td>
					<td>${esc(s.titulo)}</td>
					<td>${esc(s.materia)}</td>
					<td>${esc(s.dia)} ${hora(s.hora_inicio)}-${hora(s.hora_fin)}</td>
					<td>${esc(s.fecha_solicitud)}</td>
					<td>${esc(s.aprobadas)} / ${esc(s.cupos)}</td>
					<td>${esc(s.estado)}${s.fecha_respuesta ? "<br>" + esc(s.fecha_respuesta) : ""}</td>
					<td>
						<button type="button" class="btnAprobar" ${pendiente && !lleno ? "" : "disabled"}>Aprobar</button>
						<button type="button" class="btnRechazar" ${pendiente ? "" : "disabled"}>Rechazar</button>
					</td>
				`;
				tabla.appendChild(fila);

				async function responder(accion) {
					const datos = new FormData();
					datos.append("id_solicitud", s.id_solicitud);
					datos.append("accion", accion);
					const res = await pedir(API + "responder_solicitud.php", { method: "POST", body: datos });
					alert(res.message);
					verSolicitudes();
					verTutorias();
					cargarEstadisticas();
				}

				fila.querySelector(".btnAprobar").addEventListener("click", function() { responder("aprobar"); });
				fila.querySelector(".btnRechazar").addEventListener("click", function() { responder("rechazar"); });
			});
		}

		document.getElementById("btnSolicitudes").addEventListener("click", function() {
			document.getElementById("modalSolicitudes").showModal();
			verSolicitudes();
		});
		document.getElementById("btnFiltrarSol").addEventListener("click", verSolicitudes);

		async function verEnCurso() {
			const r = await pedir(API + "ver_en_curso.php?" + qs({
				fecha_inicio: document.getElementById("eFechaInicio").value,
				fecha_fin: document.getElementById("eFechaFin").value,
				id_materia: document.getElementById("eMateria").value
			}));

			const cont = document.getElementById("listaEnCurso");
			cont.innerHTML = "";

			if (r.code !== 200) {
				alert(r.message);
				return;
			}
			if (r.tutorias.length === 0) {
				cont.textContent = "No tiene tutorías en curso";
				return;
			}

			r.tutorias.forEach(function(t) {
				const asignados = t.horarios.filter(function(h) { return h.estado === "asignado"; });
				const bloque = document.createElement("article");
				bloque.innerHTML = `
					<h3>${esc(t.titulo)} <small>(${esc(t.materia)})</small></h3>
					<p>${esc(t.descripcion)}<br>${esc(t.fecha_inicio)} a ${esc(t.fecha_fin)} · Cupos ${esc(t.aprobadas)} / ${esc(t.cupos)}</p>
					<ul>
						${t.horarios.map(function(h) {
							return `<li>${esc(h.dia)} ${hora(h.hora_inicio)}-${hora(h.hora_fin)}: ${h.estado === "asignado" ? esc(h.estudiante) : "libre"}</li>`;
						}).join("")}
					</ul>
					${asignados.length === 0 ? "<p>Aún sin estudiantes aprobados.</p>" : ""}
				`;
				cont.appendChild(bloque);
			});
		}

		document.getElementById("btnEnCurso").addEventListener("click", function() {
			document.getElementById("modalEnCurso").showModal();
			verEnCurso();
		});
		document.getElementById("btnFiltrarEnCurso").addEventListener("click", verEnCurso);

		cargarMaterias();
		verTutorias();
		cargarEstadisticas();
	</script>
	<script src="../JS/tablas-responsive.js"></script>
</body>
</html>
