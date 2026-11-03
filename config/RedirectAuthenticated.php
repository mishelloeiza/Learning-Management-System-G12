<?php
require_once __DIR__ . '/Configuration.php';
//mapear roles
$dashboards = [
    '1' => '/estudiante/stu_dashboard',
    '2' => '/tutor/tut_dashboard',
    '3' => '/admin/adm_dashboard',
];
$rol = (string) ($_SESSION['rol'] ?? '');

//Comprobar session activa
if (!empty($_SESSION['id']) && isset($dashboards[$rol])) {
    //Calcular rutas activas
    $base = str_replace('\\', '/', dirname(__DIR__));
    $documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $prefix = ($documentRoot !== '' && strpos($base, $documentRoot) === 0)
        ? substr($base, strlen($documentRoot)) : '';
    //redireccionar
    header('Location: ' . $prefix . $dashboards[$rol], true, 302);
    exit;
}
