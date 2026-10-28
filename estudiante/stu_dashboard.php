<?php
	require_once("../api/estudiante/stu_verificar_sesion.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard estudiante</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../CSS/auth.css">
	<link rel="stylesheet" href="../CSS/panel.css">
	<link rel="stylesheet" href="../CSS/navbar.css">
	<script src="../JS/navbar.js" defer></script>
</head>
<body>
	<header class="site-header">
		<nav class="navbar" aria-label="Navegación principal">
			<button class="navbar__toggle" type="button" aria-expanded="false" aria-controls="menuPrincipal" aria-label="Abrir menú">
				<span class="navbar__bars" aria-hidden="true"></span>
			</button>
			<ul class="navbar__menu" id="menuPrincipal">
				<li><a href="./stu_perfil.php">Mi perfil</a></li>
				<li><button class="navbar__action" id="CerrarS" type="button">Cerrar Sesión</button></li>
			</ul>
		</nav>
	</header>

	<main>
		<h1>Estudiante</h1>
		<p>Encuentra una tutoría o revisa el estado de tus solicitudes.</p>

		<section class="resumen-dashboard" aria-live="polite">

			<article class="tarjeta-resumen">
				<h3>Tutorías disponibles</h3>
				<p class="resumen-cantidad" data-stat="disponibles">–</p>
				<small data-desc="disponibles">Tutorías que puedes consultar</small>
				<button type="button" class="abrirTutorias">Consultar tutorías</button>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes pendientes</h3>
				<p class="resumen-cantidad" data-stat="pendientes">–</p>
				<small data-desc="pendientes">Solicitudes esperando respuesta</small>
				<button type="button" class="abrirSolicitudes">Ver solicitudes</button>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes aprobadas</h3>
				<p class="resumen-cantidad" data-stat="aprobadas">–</p>
				<small data-desc="aprobadas">Solicitudes aceptadas por el tutor</small>
				<button type="button" class="abrirSolicitudes">Ver solicitudes</button>
			</article>

			<article class="tarjeta-resumen">
				<h3>Solicitudes rechazadas</h3>
				<p class="resumen-cantidad" data-stat="rechazadas">–</p>
				<small data-desc="rechazadas">Solicitudes que el tutor rechazó</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Total de solicitudes</h3>
				<p class="resumen-cantidad" data-stat="total">–</p>
				<small data-desc="total">Solicitudes que has enviado</small>
			</article>

			<article class="tarjeta-resumen">
				<h3>Mis tutorías vigentes</h3>
				<p class="resumen-cantidad" data-stat="tutorias_vigentes">–</p>
				<small data-desc="tutorias_vigentes">Tutorías aprobadas que siguen activas</small>
			</article>

		</section>
	</main>

	<dialog id="ModalTutorias">
		<div class="dialog-head">
			<h2>Tutorías disponibles</h2>
			<button type="button" class="close" data-close="ModalTutorias" aria-label="Cerrar">&times;</button>
		</div>
		<div class="dialog-body">
			<div id="ListaTutoriasVista">
				<form class="filters" id="FiltrosTutorias">
					<label>Fecha de inicio<input type="date" name="fecha_inicio"></label>
					<label>Fecha de fin<input type="date" name="fecha_fin"></label>
					<label>Tutor<select name="id_tutor"><option value="">Todos</option></select></label>
					<label>Materia<select name="id_materia"><option value="">Todas</option></select></label>
					<div class="filter-actions"><button type="button" class="primary" id="FiltrarTutorias">Filtrar</button></div>
				</form>
				<div class="scroll-list" id="ListaTutorias" aria-live="polite"></div>
			</div>
			<section id="DetalleTutoriaVista" hidden>
				<button type="button" class="secondary" id="VolverTutorias">Volver</button>
				<div id="DetalleTutoria"></div>
			</section>
		</div>
	</dialog>

	<dialog id="ModalSolicitudes">
		<div class="dialog-head">
			<h2>Estado de mis solicitudes</h2>
			<button type="button" class="close" data-close="ModalSolicitudes" aria-label="Cerrar">&times;</button>
		</div>
		<div class="dialog-body">
			<form class="filters" id="FiltrosSolicitudes">
				<label>Fecha de inicio<input type="date" name="fecha_inicio"></label>
				<label>Fecha de fin<input type="date" name="fecha_fin"></label>
				<label>Tutor<select name="id_tutor"><option value="">Todos</option></select></label>
				<label>Materia<select name="id_materia"><option value="">Todas</option></select></label>
				<div class="filter-actions"><button type="button" class="primary" id="FiltrarSolicitudes">Filtrar</button></div>
			</form>
			<div class="scroll-list" id="ListaSolicitudes" aria-live="polite"></div>
		</div>
	</dialog>

	<script>
		const apiTutorias = "../api/estudiante/stu_tutorias/";
		const tamanoPagina = 5;
		let offsetTutorias = 0;
		let offsetSolicitudes = 0;
		let cargandoTutorias = false;
		let cargandoSolicitudes = false;
		let hayMasTutorias = true;
		let hayMasSolicitudes = true;
		let tutoriaActual = null;

		function escapar(valor) {
			return String(valor ?? "").replace(/[&<>"']/g, caracter => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"}[caracter]));
		}

		async function consultar(url, opciones = {}) {
			const respuesta = await fetch(url, opciones);
			const resultado = await respuesta.json();
			if (!respuesta.ok || resultado.code !== 200) throw new Error(resultado.message || "No se pudo completar la operación");
			return resultado;
		}

		async function cargarEstadisticas() {
			try {
				const resultado = await consultar("../api/estudiante/stu_estadisticas.php");
				document.querySelectorAll("[data-stat]").forEach(el => {
					el.textContent = resultado.estadisticas[el.dataset.stat] ?? "–";
				});
			} catch (error) {
				console.error(error);
			}
		}

		function filtrosDe(id) {
			const form = document.getElementById(id);
			return new URLSearchParams(new FormData(form));
		}

		async function cargarFiltros() {
			try {
				const resultado = await consultar(apiTutorias + "filtros.php");
				["FiltrosTutorias", "FiltrosSolicitudes"].forEach(id => {
					const form = document.getElementById(id);
					resultado.tutores.forEach(tutor => form.elements.id_tutor.add(new Option(tutor.tutor, tutor.id_tutor)));
					resultado.materias.forEach(materia => form.elements.id_materia.add(new Option(materia.materia, materia.id_materia)));
				});
			} catch (error) {
				console.error(error);
			}
		}

		function abrirListaTutorias() {
			document.getElementById("ListaTutoriasVista").hidden = false;
			document.getElementById("DetalleTutoriaVista").hidden = true;
			tutoriaActual = null;
		}

		function reiniciarTutorias() {
			offsetTutorias = 0;
			hayMasTutorias = true;
			document.getElementById("ListaTutorias").innerHTML = "";
			cargarTutorias();
		}

		async function cargarTutorias() {
			if (cargandoTutorias || !hayMasTutorias) return;
			cargandoTutorias = true;
			const lista = document.getElementById("ListaTutorias");
			if (offsetTutorias === 0) lista.innerHTML = '<p class="empty">Cargando tutorías...</p>';
			try {
				const parametros = filtrosDe("FiltrosTutorias");
				parametros.set("offset", offsetTutorias);
				const resultado = await consultar(apiTutorias + "ver_tutorias.php?" + parametros.toString());
				if (offsetTutorias === 0) lista.innerHTML = "";
				if (resultado.tutorias.length === 0 && offsetTutorias === 0) lista.innerHTML = '<p class="empty">No hay tutorías disponibles con esos filtros.</p>';
				resultado.tutorias.forEach(tutoria => {
					const elemento = document.createElement("article");
					elemento.className = "tutoria-item";
					elemento.innerHTML = `<button type="button" data-tutoria="${escapar(tutoria.id_tutoria)}"><h3>${escapar(tutoria.titulo)}</h3><span>${escapar(tutoria.materia)} · ${escapar(tutoria.tutor)}</span><br><span class="muted">${escapar(tutoria.fecha_inicio)} al ${escapar(tutoria.fecha_fin)}</span></button>`;
					lista.appendChild(elemento);
				});
				hayMasTutorias = resultado.hay_mas;
				offsetTutorias += tamanoPagina;
			} catch (error) {
				lista.innerHTML = `<p class="empty">${escapar(error.message)}</p>`;
			} finally {
				cargandoTutorias = false;
			}
		}

		async function verDetalle(idTutoria) {
			try {
				const resultado = await consultar(apiTutorias + "ver_detalle.php?id_tutoria=" + encodeURIComponent(idTutoria));
				tutoriaActual = resultado.tutoria;
				mostrarDetalle();
			} catch (error) {
				alert(error.message);
			}
		}

		function mostrarDetalle() {
			const tutoria = tutoriaActual;
			const detalle = document.getElementById("DetalleTutoria");
			document.getElementById("ListaTutoriasVista").hidden = true;
			document.getElementById("DetalleTutoriaVista").hidden = false;
			let contenido = `<h3>${escapar(tutoria.titulo)}</h3><p>${escapar(tutoria.descripcion)}</p><p><strong>Materia:</strong> ${escapar(tutoria.materia)}<br><strong>Tutor:</strong> ${escapar(tutoria.tutor)}<br><strong>Fechas:</strong> ${escapar(tutoria.fecha_inicio)} al ${escapar(tutoria.fecha_fin)}</p>`;
			if (tutoria.estado_solicitud === "rechazada") {
				contenido += '<p class="notice">Esta tutoría fue rechazada y ya no se puede volver a solicitar.</p>';
			} else if (tutoria.estado_solicitud === "pendiente") {
				contenido += '<p class="notice">Ya tienes una solicitud pendiente para esta tutoría. Puedes eliminarla desde aquí o en Mis solicitudes.</p>';
			} else if (tutoria.estado_solicitud === "aprobada") {
				contenido += '<p class="notice">Ya tienes una solicitud aprobada para esta tutoría.</p>';
			}
			contenido += '<h4>Horarios</h4>';
			tutoria.horarios.forEach(horario => {
				const hora = `${escapar(horario.hora_inicio).slice(0, 5)} - ${escapar(horario.hora_fin).slice(0, 5)}`;
				const accion = horario.estado_solicitud === "pendiente"
					? `<button type="button" class="secondary" data-eliminar="${escapar(horario.id_solicitud)}">Eliminar solicitud</button>`
					: (!tutoria.estado_solicitud && horario.disponibilidad === "disponible"
						? `<button type="button" class="primary" data-solicitar="${escapar(horario.id_horario)}">Solicitar</button>` : "");
				const estado = horario.estado_solicitud === "pendiente" ? "Pendiente" : horario.estado_solicitud === "aprobada" ? "Aprobada" : horario.disponibilidad === "reservado" ? "Reservado" : "Disponible";
				contenido += `<div class="slot"><div><strong>${escapar(horario.dia)}</strong> · ${hora}<br><span class="muted">${estado}</span></div>${accion}</div>`;
			});
			detalle.innerHTML = contenido;
		}

		async function solicitar(idHorario) {
			const datos = new FormData();
			datos.append("id_horario", idHorario);
			try {
				await consultar(apiTutorias + "crear_solicitud.php", {method: "POST", body: datos});
				cargarEstadisticas();
				await verDetalle(tutoriaActual.id_tutoria);
			} catch (error) {
				alert(error.message);
				await verDetalle(tutoriaActual.id_tutoria);
			}
		}

		async function eliminarSolicitud(idSolicitud, actualizarDetalle) {
			if (!confirm("¿Deseas eliminar esta solicitud pendiente?")) return;
			const datos = new FormData();
			datos.append("id_solicitud", idSolicitud);
			try {
				await consultar(apiTutorias + "eliminar_solicitud.php", {method: "POST", body: datos});
				cargarEstadisticas();
				if (actualizarDetalle && tutoriaActual) await verDetalle(tutoriaActual.id_tutoria);
				if (!actualizarDetalle) reiniciarSolicitudes();
			} catch (error) {
				alert(error.message);
			}
		}

		function reiniciarSolicitudes() {
			offsetSolicitudes = 0;
			hayMasSolicitudes = true;
			document.getElementById("ListaSolicitudes").innerHTML = "";
			cargarSolicitudes();
		}

		async function cargarSolicitudes() {
			if (cargandoSolicitudes || !hayMasSolicitudes) return;
			cargandoSolicitudes = true;
			const lista = document.getElementById("ListaSolicitudes");
			if (offsetSolicitudes === 0) lista.innerHTML = '<p class="empty">Cargando solicitudes...</p>';
			try {
				const parametros = filtrosDe("FiltrosSolicitudes");
				parametros.set("offset", offsetSolicitudes);
				const resultado = await consultar(apiTutorias + "ver_solicitudes.php?" + parametros.toString());
				if (offsetSolicitudes === 0) lista.innerHTML = "";
				if (resultado.solicitudes.length === 0 && offsetSolicitudes === 0) lista.innerHTML = '<p class="empty">No tienes solicitudes con esos filtros.</p>';
				resultado.solicitudes.forEach(solicitud => {
					const elemento = document.createElement("article");
					elemento.className = "solicitud-item";
					const eliminar = solicitud.estado === "pendiente" ? `<button type="button" class="secondary" data-eliminar="${escapar(solicitud.id_solicitud)}">Eliminar</button>` : "";
					elemento.innerHTML = `<h3>${escapar(solicitud.titulo)} <span class="status" data-state="${escapar(solicitud.estado)}">${escapar(solicitud.estado)}</span></h3><div>${escapar(solicitud.materia)} · ${escapar(solicitud.tutor)}</div><div class="muted">${escapar(solicitud.dia)} ${escapar(solicitud.hora_inicio).slice(0, 5)} - ${escapar(solicitud.hora_fin).slice(0, 5)} · ${escapar(solicitud.fecha_inicio)} al ${escapar(solicitud.fecha_fin)}</div><div class="muted">Solicitud: ${escapar(solicitud.fecha_solicitud)}</div>${eliminar}`;
					lista.appendChild(elemento);
				});
				hayMasSolicitudes = resultado.hay_mas;
				offsetSolicitudes += tamanoPagina;
			} catch (error) {
				lista.innerHTML = `<p class="empty">${escapar(error.message)}</p>`;
			} finally {
				cargandoSolicitudes = false;
			}
		}

		document.querySelectorAll(".abrirTutorias").forEach(boton => boton.addEventListener("click", () => {
			abrirListaTutorias();
			document.getElementById("ModalTutorias").showModal();
			reiniciarTutorias();
		}));
		document.querySelectorAll(".abrirSolicitudes").forEach(boton => boton.addEventListener("click", () => {
			document.getElementById("ModalSolicitudes").showModal();
			reiniciarSolicitudes();
		}));
		document.querySelectorAll("[data-close]").forEach(boton => boton.addEventListener("click", () => document.getElementById(boton.dataset.close).close()));
		document.getElementById("FiltrarTutorias").addEventListener("click", reiniciarTutorias);
		document.getElementById("FiltrarSolicitudes").addEventListener("click", reiniciarSolicitudes);
		document.getElementById("VolverTutorias").addEventListener("click", abrirListaTutorias);
		document.getElementById("ListaTutorias").addEventListener("scroll", evento => {
			const lista = evento.currentTarget;
			if (lista.scrollTop + lista.clientHeight >= lista.scrollHeight - 80) cargarTutorias();
		});
		document.getElementById("ListaSolicitudes").addEventListener("scroll", evento => {
			const lista = evento.currentTarget;
			if (lista.scrollTop + lista.clientHeight >= lista.scrollHeight - 80) cargarSolicitudes();
		});
		document.getElementById("ListaTutorias").addEventListener("click", evento => {
			const boton = evento.target.closest("[data-tutoria]");
			if (boton) verDetalle(boton.dataset.tutoria);
		});
		document.getElementById("DetalleTutoria").addEventListener("click", evento => {
			const solicitarBoton = evento.target.closest("[data-solicitar]");
			const eliminarBoton = evento.target.closest("[data-eliminar]");
			if (solicitarBoton) solicitar(solicitarBoton.dataset.solicitar);
			if (eliminarBoton) eliminarSolicitud(eliminarBoton.dataset.eliminar, true);
		});
		document.getElementById("ListaSolicitudes").addEventListener("click", evento => {
			const boton = evento.target.closest("[data-eliminar]");
			if (boton) eliminarSolicitud(boton.dataset.eliminar, false);
		});
		cargarFiltros();
		cargarEstadisticas();

		document.getElementById("CerrarS").addEventListener("click", async function() {
			try {
				const respuesta = await fetch("../api/estudiante/stu_cerrar_sesion.php", {
					method: "POST"
				});

				const resultado = await respuesta.json();

				if (resultado.code === 200){
					window.location.href = "../index.php";
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});
	</script>
</body>
</html>