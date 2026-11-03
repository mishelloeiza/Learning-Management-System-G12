<?php
require_once __DIR__ . '/Connection.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido');
}
if (!isset($_SESSION['id'], $_SESSION['rol']) || (string) $_SESSION['rol'] !== $rolReporte) {
    http_response_code(403);
    exit('Acceso no autorizado');
}

$idReporte = (int) $_SESSION['id'];
$seccionesReporte = [];

if ($rolReporte === '3') {
    $seccionesReporte = [
        ['Usuarios registrados', 'SELECT u.id_usuario AS ID, u.nombre AS Nombre, u.apellido AS Apellido, u.correo AS Correo, r.nombre AS Rol, c.nombre AS Carrera, IF(u.activo = 1, \'Activo\', \'Inactivo\') AS Estado, u.creado_en AS Registro FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol INNER JOIN carreras c ON c.id_carrera = u.id_carrera ORDER BY u.id_usuario', []],
        ['Carreras', 'SELECT id_carrera AS ID, nombre AS Carrera, descripcion AS Descripcion, IF(activo = 1, \'Activo\', \'Inactivo\') AS Estado FROM carreras ORDER BY id_carrera', []],
        ['Materias', 'SELECT m.id_materia AS ID, m.nombre AS Materia, c.nombre AS Carrera, IF(m.activo = 1, \'Activo\', \'Inactivo\') AS Estado FROM materias m INNER JOIN carreras c ON c.id_carrera = m.id_carrera ORDER BY m.id_materia', []],
        ['Tutorías', 'SELECT t.id_tutoria AS ID, t.titulo AS Tutoria, m.nombre AS Materia, CONCAT(u.nombre, \' \', u.apellido) AS Tutor, t.estado AS Estado, t.fecha_inicio AS Inicio, t.fecha_fin AS Fin, t.cupos AS Cupos FROM tutorias t INNER JOIN materias m ON m.id_materia = t.id_materia INNER JOIN usuarios u ON u.id_usuario = t.id_tutor ORDER BY t.id_tutoria', []],
        ['Horarios', 'SELECT h.id_horarios AS ID, t.titulo AS Tutoria, h.dias_curso AS Dia, h.hora_inicio AS Inicio, h.hora_fin AS Fin, h.estado AS Estado FROM horarios h INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria ORDER BY h.id_horarios', []],
        ['Solicitudes', 'SELECT s.id_solicitud AS ID, t.titulo AS Tutoria, CONCAT(e.nombre, \' \', e.apellido) AS Estudiante, CONCAT(u.nombre, \' \', u.apellido) AS Tutor, s.estado AS Estado, s.fecha_solicitud AS Solicitud, s.fecha_respuesta AS Respuesta FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria INNER JOIN usuarios e ON e.id_usuario = s.id_usuario INNER JOIN usuarios u ON u.id_usuario = t.id_tutor ORDER BY s.id_solicitud', []],
    ];
} elseif ($rolReporte === '2') {
    $seccionesReporte = [
        ['Mi perfil', 'SELECT id_usuario AS ID, nombre AS Nombre, apellido AS Apellido, correo AS Correo FROM usuarios_2 WHERE id_usuario = ?', [$idReporte]],
        ['Mis tutorías', 'SELECT t.id_tutoria AS ID, t.titulo AS Tutoria, m.nombre AS Materia, t.estado AS Estado, t.fecha_inicio AS Inicio, t.fecha_fin AS Fin, t.cupos AS Cupos FROM tutorias t INNER JOIN materias m ON m.id_materia = t.id_materia WHERE t.id_tutor = ? ORDER BY t.id_tutoria', [$idReporte]],
        ['Mis horarios', 'SELECT h.id_horarios AS ID, t.titulo AS Tutoria, h.dias_curso AS Dia, h.hora_inicio AS Inicio, h.hora_fin AS Fin, h.estado AS Estado FROM horarios h INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria WHERE t.id_tutor = ? ORDER BY h.id_horarios', [$idReporte]],
        ['Solicitudes recibidas', 'SELECT s.id_solicitud AS ID, t.titulo AS Tutoria, CONCAT(e.nombre, \' \', e.apellido) AS Estudiante, s.estado AS Estado, s.fecha_solicitud AS Solicitud, s.fecha_respuesta AS Respuesta FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria INNER JOIN estudiantes_info e ON e.id_usuario = s.id_usuario WHERE t.id_tutor = ? ORDER BY s.id_solicitud', [$idReporte]],
    ];
} elseif ($rolReporte === '1') {
    $seccionesReporte = [
        ['Mi perfil', 'SELECT id_usuario AS ID, nombre AS Nombre, apellido AS Apellido, correo AS Correo FROM usuarios_1 WHERE id_usuario = ?', [$idReporte]],
        ['Mis solicitudes', 'SELECT s.id_solicitud AS ID, t.titulo AS Tutoria, m.nombre AS Materia, CONCAT(u.nombre, \' \', u.apellido) AS Tutor, s.estado AS Estado, s.fecha_solicitud AS Solicitud, s.fecha_respuesta AS Respuesta FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria INNER JOIN materias m ON m.id_materia = t.id_materia INNER JOIN usuarios_2 u ON u.id_usuario = t.id_tutor WHERE s.id_usuario = ? ORDER BY s.id_solicitud', [$idReporte]],
        ['Mis horarios solicitados', 'SELECT s.id_solicitud AS Solicitud, t.titulo AS Tutoria, h.dias_curso AS Dia, h.hora_inicio AS Inicio, h.hora_fin AS Fin, h.estado AS Estado_horario, s.estado AS Estado_solicitud FROM solicitudes s INNER JOIN horarios h ON h.id_horarios = s.id_horario INNER JOIN tutorias t ON t.id_tutoria = h.id_tutoria WHERE s.id_usuario = ? ORDER BY s.id_solicitud', [$idReporte]],
    ];
}

try {
    $conexionReporte = new Connection($rolReporte);
    $datosReporte = [];
    foreach ($seccionesReporte as [$tituloReporte, $sqlReporte, $parametrosReporte]) {
        $consultaReporte = $conexionReporte->prepare($sqlReporte);
        $consultaReporte->execute($parametrosReporte);
        $encabezadosReporte = [];
        for ($i = 0; $i < $consultaReporte->columnCount(); $i++) {
            $encabezadosReporte[] = $consultaReporte->getColumnMeta($i)['name'];
        }
        $datosReporte[] = [$tituloReporte, $encabezadosReporte, $consultaReporte->fetchAll(PDO::FETCH_NUM)];
    }
} catch (PDOException $error) {
    http_response_code(500);
    exit('No fue posible generar el reporte general. Comprueba los permisos de lectura en MySQL.');
}

require_once __DIR__ . '/PdfReporte.php';
$nombreRolReporte = ['1' => 'Estudiante', '2' => 'Tutor', '3' => 'Administrador'][$rolReporte];
$documentoReporte = new PdfReporte($nombreRolReporte);
foreach ($datosReporte as [$tituloReporte, $encabezadosReporte, $filasReporte]) {
    $documentoReporte->agregarSeccion($tituloReporte, $encabezadosReporte, $filasReporte);
}
$documentoReporte->descargar('reporte_general_' . strtolower($nombreRolReporte) . '_' . date('Y-m-d'));
exit;
