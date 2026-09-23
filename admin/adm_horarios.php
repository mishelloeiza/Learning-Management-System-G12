<?php
	require_once("../api/admin/adm_verificar_sesion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Horarios</title>
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
        <h1>Administración Horarios</h1>
    </main>
    <section>
        <h2>Registrar Nuevo Horario</h2>
        <form id="FormHor" method="POST">
            <label for="HoraInicio">Hora de Incio</label>
            <input type="time" id="HoraInicio" name="HoraInicio" required>
            <label for="HoraFin">Hora de Finalización</label>
            <input type="time" id="HoraFin" name="HoraFin" required>
            <label for="DiasCurso">Días del Curso</label>
            <select name="DiasCurso" id="DiasCurso" required>
                <option value="">Seleccione un día</option>
                <option value="domingo">Domingo</option>
                <option value="lunes">Lunes</option>
                <option value="martes">Martes</option>
                <option value="miercoles">Miercoles</option>
                <option value="jueves">Jueves</option>
                <option value="viernes">Viernes</option>
                <option value="sabado">Sabado</option>
            </select>
            <label for="IdTutoria">Id Tutoria</label>
            <input type="number" id="IdTutoria" name="IdTutoria" placeholder="Ingrese el ID de la tutoria" required>
            <label for="Estado">Estado</label>
            <select name="Estado" id="Estado" disabled>
                <option value="disponible">Disponible</option>
                <option value="asignado">Asignado</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>    
        </form>
    </section>

    <section>
        <h2>Lista de Horarios</h2>
        <label for="buscardia">Buscar por Dia</label>
        <select name="buscardia" id="buscardia" required>
                <option value="">Seleccione un día</option>
                <option value="domingo">Domingo</option>
                <option value="lunes">Lunes</option>
                <option value="martes">Martes</option>
                <option value="miercoles">Miercoles</option>
                <option value="jueves">Jueves</option>
                <option value="viernes">Viernes</option>
                <option value="sabado">Sabado</option>
            </select>
        <label for="buscarmateria">Buscar por Materia</label>
        <input type="text" id="buscarmateria" name="buscarmateria" placeholder="Ej. Programación">
        <button type="button" id="btnBuscar">Buscar</button>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Dia</th>
                    <th>Materia</th>
                    <th>Estado</th>
                    <th>Id Tutoria</th>
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
        //Cerrar Sesión
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

        //Crear y editar horarios
        document.getElementById("FormHor").addEventListener("submit", async function(e) {
            e.preventDefault();
            const formulario = new FormData(this);
            try {
                if (editando == true) {
                    formulario.append("IdHorarios", idhorarioselect);
                    formulario.append("Estado", document.getElementById("Estado").value);

                    const respuesta_e = await fetch("../api/admin/adm_horarios/editar_horarios.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado_e = await respuesta_e.json();

                    if (resultado_e.code === 200) {
                        alert(resultado_e.message);
                        this.reset();
                        verHorarios();
                        idhorarioselect = null;
                        editando = false;
                        document.getElementById("Estado").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }else{
                        alert(resultado_e.message);
                        idhorarioselect = null;
                        editando = false;
                        this.reset();
                        document.getElementById("Estado").disabled = true;
                        document.getElementById("btnGuardar").textContent = "Registrar";
                    }
                }else{
                    const respuesta = await fetch("../api/admin/adm_horarios/crear_horarios.php", {
                        method: "POST",
                        body: formulario
                    });

                    const resultado = await respuesta.json();

                    if (resultado.code === 200) {
                        alert(resultado.message);
                        this.reset();
                        verHorarios();
                    }else{
                        alert(resultado.message);
                    }
                }
            } catch (error) {
                console.log(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        //Variables para editar y eliminar datos
        let idhorarioselect = null;
        let id_horarielim = null;
        let editando = false;

        //Visualizar horarios
        async function verHorarios(buscardia = "", buscarmateria = "") {
            try {
                const url = "../api/admin/adm_horarios/ver_horarios.php?buscardia="+encodeURIComponent(buscardia)+"&buscarmateria="+encodeURIComponent(buscarmateria);
                
                const respuesta = await fetch(url);

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    const tabla = document.querySelector("table tbody");
                    tabla.innerHTML = "";
                    resultado.horarios.forEach(function(horario) {
                        const fila = document.createElement("tr")
                        fila.innerHTML = `
                            <td>${horario.id_horarios}</td> 
                            <td>${horario.hora_inicio}</td> 
                            <td>${horario.hora_fin}</td> 
                            <td>${horario.dias_curso}</td> 
                            <td>${horario.materia}</td> 
                            <td>${horario.estado}</td> 
                            <td>${horario.id_tutoria}</td>
                            <td>
                                <button type="button" class="btnSeleccionar">Seleccionar</button>
                                <button type="button" class="btnEliminar" onclick="return confirm('¿Estás seguro de que desea cancelar este horario?');">Eliminar</button>
                            </td>   
                        `; 
                        tabla.appendChild(fila);
                        
                        //Seleccionar una fila
                        const seleccionar = fila.querySelector(".btnSeleccionar");

                        seleccionar.addEventListener("click", function() {
                            idhorarioselect = horario.id_horarios;
                            document.getElementById("HoraInicio").value = horario.hora_inicio;
                            document.getElementById("HoraFin").value = horario.hora_fin;
                            document.getElementById("DiasCurso").value = horario.dias_curso;
                            document.getElementById("IdTutoria").value = horario.id_tutoria;
                            document.getElementById("Estado").value = horario.estado;
                            editando = false;
                            document.getElementById("HoraInicio").disabled = true;
                            document.getElementById("HoraFin").disabled = true;
                            document.getElementById("DiasCurso").disabled = true;
                            document.getElementById("IdTutoria").disabled = true;
                            document.getElementById("Estado").disabled = true;
                            document.getElementById("btnGuardar").disabled = true;
                        });

                        //Eliminar un horario
                        const eliminar = fila.querySelector(".btnEliminar");

                        eliminar.addEventListener("click", async function() {
                            id_horarielim = horario.id_horarios;
                            const dato = new FormData();
                            dato.append("IdHorarios", id_horarielim);
                            try {
                                const respuesta_b = await fetch("../api/admin/adm_horarios/eliminar_horarios.php", {
                                    method: "POST",
                                    body: dato
                                });

                                const resultado_b = await respuesta_b.json();

                                if (resultado_b.code === 200) {
                                    alert(resultado_b.message);
                                    verHorarios();
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
                alert("Error al cargar horarios");
            }
        }

        //Usar filtro
        document.getElementById("btnBuscar").addEventListener("click", function() {
            const buscardia = document.getElementById("buscardia").value.trim();
            const buscarmateria = document.getElementById("buscarmateria").value.trim();
            verHorarios(buscardia, buscarmateria);
        });

        //Fuciones del botón editar
        document.getElementById("btnEditar").addEventListener("click", function() {
            if (idhorarioselect === null) {
                alert("Primero debe seleccionar un horario");
                return;
            }
            document.getElementById("HoraInicio").disabled = false;
            document.getElementById("HoraFin").disabled = false;
            document.getElementById("DiasCurso").disabled = false;
            document.getElementById("IdTutoria").disabled = false;
            document.getElementById("Estado").disabled = false;
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Guardar Cambios";
            editando = true;
        });

        //Cancelar edición
        document.getElementById("btnCancelar").addEventListener("click", function() {
            idhorarioselect = null;
            editando = false;
            document.getElementById("FormHor").reset();
            document.getElementById("HoraInicio").disabled = false;
            document.getElementById("HoraFin").disabled = false;
            document.getElementById("DiasCurso").disabled = false;
            document.getElementById("IdTutoria").disabled = false;
            document.getElementById("Estado").disabled = false;
            document.getElementById("btnGuardar").disabled = false;
            document.getElementById("btnGuardar").textContent = "Registrar";
        });

        //Cargar función de ver horarios
        verHorarios();
    </script>
</body>
</html>