<?php
/**
 * header.php
 * Para qué sirve: Encabezado HTML común, menú de navegación por rol y visualización de alertas.
 * Trabaja con: config/sesion.php y todas las vistas del sistema.
 * Equivale en NetBeans a: Barra de menú o cabecera visual de las ventanas Swing.
 */

$usuario = usuarioActual();
$rol = $usuario['rol'] ?? '';
$mensajeExito = obtenerFlash('exito');
$mensajeError = obtenerFlash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'SmartMed Web', ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Tipografía Inter desde Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Hoja de estilos única del proyecto -->
    <link rel="stylesheet" href="assets/css/estilos.css">
    <!-- Librería local JsBarcode para generar códigos de barra offline -->
    <script src="assets/js/JsBarcode.all.min.js"></script>
</head>
<body>

<?php if ($usuario): ?>
    <!-- Barra superior de navegación para usuarios que iniciaron sesión -->
    <header class="topbar-clinica no-print">
        <div class="topbar-logo">
            <span style="font-size: 1.4rem;">🩺</span>
            <strong>SmartMed Web</strong>
        </div>

        <nav class="topbar-nav">
            <?php if ($rol === 'paciente'): ?>
                <a href="index.php?ruta=paciente/agendar">Agendar Cita</a>
                <a href="index.php?ruta=paciente/citas">Mis Citas</a>
                <a href="index.php?ruta=paciente/recetas">Mis Recetas</a>
            <?php elseif ($rol === 'recepcion'): ?>
                <a href="index.php?ruta=recepcion/control">Control de Llegada</a>
                <a href="index.php?ruta=recepcion/citas_dia">Citas del Día</a>
                <a href="index.php?ruta=reportes/citas">Reporte de Citas</a>
            <?php elseif ($rol === 'medico'): ?>
                <a href="index.php?ruta=medico/agenda">Mi Agenda</a>
                <a href="index.php?ruta=reportes/historial">Historial de Pacientes</a>
                <a href="index.php?ruta=reportes/citas">Reporte de Citas</a>
            <?php endif; ?>
        </nav>

        <div class="topbar-usuario">
            <span><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge-rol"><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></span>
            <a href="index.php?ruta=logout" class="btn-salir" title="Cerrar Sesión">Salir</a>
        </div>
    </header>
<?php endif; ?>

<!-- Contenedor principal de la página -->
<main class="contenedor-principal">
    <!-- Mensajes de alerta temporales -->
    <?php if ($mensajeExito): ?>
        <div class="alerta alerta-exito">
            <span>✓</span> <?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <div class="alerta alerta-error">
            <span>✕</span> <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

