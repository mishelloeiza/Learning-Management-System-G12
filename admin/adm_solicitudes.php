<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Solicitudes</title>
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
		<h1>Administracion solicitudes</h1>
	</main>
    <section>
        <h2>Registrar nueva solicitud</h2>
        <form id="FormSol" method="POST">
            <label for="IdHorario">ID Horario</label>
            <input type="number" id="IdHorario" name="IdHorario" placeholder="Ingrese el ID del horario" required>
            <label for="IdUsuario">ID Usuario</label>
            <input type="number" id="IdUsuario" name="IdUsuario" placeholder="Ingrese el ID del usuario" required>
            <label for="FechaRespuesta">Fecha respuesta</label>
            <input type="date" id="FechaRespuesta" name="FechaRespuesta" disabled>
            <label for="Estado">Estado</label>
            <select id="Estado" name="Estado" disabled>
                <option value="pendiente">Pendiente</option>
                <option value="aprobada">Aprobada</option>
                <option value="rechazada">Rechazada</option>
            </select>
            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de solicitudes</h2>
        <label for="buscarEstado">Buscar por estado</label>
        <select id="buscarEstado" name="buscarEstado">
                <option value="pendiente">Pendiente</option>
                <option value="aprobada">Aprobada</option>
                <option value="rechazada">Rechazada</option>
        </select>
        <label for="buscarMateria">Buscar por materia</label>
        <input type="text" id="buscarMateria" name="buscarMateria" placeholder="Ej: Programacion">
        <label for="buscarDesde">Desde</label>
        <input type="date" id="buscarDesde" name="buscarDesde">
        <label for="buscarHasta">Hasta</label>
        <input type="date" id="buscarHasta" name="buscarHasta">
        <button type="button" id="btnBuscar">Buscar</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Fecha solicitud</th>
                    <th>Fecha respuesta</th>
                    <th>Materia</th>
                    <th>ID Horario</th>
                    <th>ID Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <td></td>
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

				if (resultado.code == 200) {
					window.location.href = "../prin_dashboard.php";
				}else{
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrio un error al cerrar sesión");
			}
		});

        //Crear y editar solicitud
        document.getElementById("FormSol").addEventListener("submit", async function(e) {
            e.preventDefault();
            const formulario = new FormData(this);
            try {
                if (editando == true) {
                    formulario.append("IdSolicitud", idsolicitudselect);
                    formulario.append("Estado", document.getElementById("Estado").value);

                    const respuesta_e = await fetch("../api/admin/adm_solicitudes/editar_solicitudes.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado_e = await respuesta_e.json();

                    if (resultado_e.code === 200) {
                        alert(resultado_e.message);
                        this.reset();
                        verSolicitudes();
                        idsolicitudselect = null;
                        editando = false;
                        document.getElementById("FechaRespuesta").disabled = true;
                        document.getElementById("Estado").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }else{
                        alert(resultado_e.message);
                        idsolicitudselect = null;
                        editando = false;
                        this.reset();
                        document.getElementById("FechaRespuesta").disabled = true;
                        document.getElementById("Estado").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }
                }else{
                    const respuesta = await fetch("../api/admin/adm_solicitudes/crear_solicitudes.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado = await respuesta.json();

                    if (resultado.code === 200) {
                        alert(resultado.message);
                        this.reset();
                        verSolicitudes();
                    }else{
                        alert(resultado.message);
                    }
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        //Variables para Editar y Eliminar datos
        let idsolicitudselect = null;
        let id_solicitudelim = null;
        let editando = false;

        //Visualizar solicitudes
        async function verSolicitudes(estado = "", materia = "", fechainicio = "", fechafin = "") {
            try {
                const url = "../api/admin/adm_solicitudes/ver_solicitudes.php?estado="+encodeURIComponent(estado)+"&materia="+encodeURIComponent(materia)+"&fechainicio="+encodeURIComponent(fechainicio)+"&fechafin="+encodeURIComponent(fechafin);

                const respuesta = await fetch(url);

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    const tabla = document.querySelector("table tbody");
                    tabla.innerHTML = "";
                    resultado.solicitudes.forEach(function(solicitud) {
                        const fila = document.createElement("tr");
                        fila.innerHTML = `
                            <td>${solicitud.id_solicitud}</td>
                            <td>${solicitud.estado}</td>
                            <td>${solicitud.fecha_solicitud}</td>
                            <td>${solicitud.fecha_respuesta ?? ""}</td>
                            <td>${solicitud.materia}</td>
                            <td>${solicitud.id_horario}</td>
                            <td>${solicitud.id_usuario}</td>
                            <td>
                                <button type="button" class="btnSeleccionar">Seleccionar</button>
                                <button type="button" class="btnEliminar" onclick="return confirm('¿Estás seguro de que deseas rechazar esta solicitud?');">Eliminar</button>
                            </td>
                        `;
                        tabla.appendChild(fila);

                        //Seleccionar una fila
                        const seleccionar = fila.querySelector(".btnSeleccionar");

                        seleccionar.addEventListener("click", function() {
                            idsolicitudselect = solicitud.id_solicitud;
                            document.getElementById("IdHorario").value = solicitud.id_horario;
                            document.getElementById("IdUsuario").value = solicitud.id_usuario;
                            document.getElementById("FechaRespuesta").value = solicitud.fecha_respuesta ?? "";
                            document.getElementById("Estado").value = solicitud.estado;
                            editando = false;
                            document.getElementById("IdHorario").disabled = true;
                            document.getElementById("IdUsuario").disabled = true;
                            document.getElementById("FechaRespuesta").disabled = true;
                            document.getElementById("Estado").disabled = true;
                            document.getElementById("btnGuardar").disabled = true;
                        });

                        //Eliminar una solicitud
                        const eliminar = fila.querySelector(".btnEliminar");

                        eliminar.addEventListener("click", async function() {
                            id_solicitudelim = solicitud.id_solicitud;
                            const dato = new FormData();
                            dato.append("IdSolicitud", id_solicitudelim);
                            try {
                                const respuesta_b = await fetch("../api/admin/adm_solicitudes/eliminar_solicitudes.php", {
                                    method: "POST",
                                    body: dato
                                });

                                const resultado_b = await respuesta_b.json();

                                if (resultado_b.code === 200) {
                                    alert(resultado_b.message);
                                    verSolicitudes();
                                }else{
                                    alert(resultado_b.message);
                                }

                            } catch (error) {
                                console.log(error);
                                alert("Error al comunicarse con el servidor");
                            }
                        });
                    });
                }else{
                    alert(resultado.message);
                }
            } catch (error) {
                console.log(error);
                alert("Error al cargar solicitudes");
            }
        }

        //Usar filtro
        document.getElementById("btnBuscar").addEventListener("click", function() {
            const estado = document.getElementById("buscarEstado").value.trim();
            const materia = document.getElementById("buscarMateria").value.trim();
            const fechainicio = document.getElementById("buscarDesde").value;
            const fechafin = document.getElementById("buscarHasta").value;
            verSolicitudes(estado, materia, fechainicio, fechafin);
        });

        //Funciones del boton editar
        document.getElementById("btnEditar").addEventListener("click", function() {
            if (idsolicitudselect === null) {
                alert("Primero debe seleccionar una solicitud");
                return;
            }
            document.getElementById("IdHorario").disabled = false;
            document.getElementById("IdUsuario").disabled = false;
            document.getElementById("FechaRespuesta").disabled = false;
            document.getElementById("Estado").disabled = false;

            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            editando = true;
        });

        //Cancelar edicion
        document.getElementById("btnCancelar").addEventListener("click", async function() {
            idsolicitudselect = null;
            editando = false;

            document.getElementById("FormSol").reset();

            document.getElementById("IdHorario").disabled = false;
            document.getElementById("IdUsuario").disabled = false;
            document.getElementById("FechaRespuesta").disabled = true;
            document.getElementById("Estado").disabled = true;

            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Registrar";
        });

        //Cargar funcion de ver solicitudes
        verSolicitudes();
    </script>
</body>
</html>