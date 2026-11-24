<?php
/**
 * historial_paciente.php
 * Para qué sirve: Muestra el expediente clínico completo del paciente con sus diagnósticos médicos.
 * Trabaja con: controllers/ReporteController.php y views/reportes/cabecera_reporte.php.
 * Equivale en NetBeans a: ReporteHistorialClinico.jrxml
 */
require_once __DIR__ . '/../header.php';

// Preparar datos para la cabecera unificada
$tituloReporte = "SMARTMED - HISTORIAL CLÍNICO";
$subtituloReporte = "Expediente médico de consultas, diagnósticos clínicos y notas de evolución";
if ($cliente) {
    $contextoReporte = "Paciente: <strong>" . htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') . "</strong> (C.I. " . htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8') . " &bull; Tel: " . htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8') . ") &bull; Consultas: " . count($historiales);
    $codigoEmision = "EXP-" . htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8');
} else {
    $contextoReporte = "Historial General del Consultorio &bull; Total Atenciones Registradas: " . count($historiales);
    $codigoEmision = null;
}

// Renderizar cabecera superior unificada
require __DIR__ . '/cabecera_reporte.php';
?>

<!-- Barra de acciones rápidas (oculta al imprimir) -->
<div class="no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
    <span style="color:#64748B; font-size:0.88rem;">Busque el expediente por cédula del paciente o imprima la ficha completa.</span>
    <button type="button" class="btn btn-primario" onclick="window.print()">🖨 Imprimir Historial</button>
</div>

<!-- Filtro por cédula (oculto al imprimir) -->
<div class="tarjeta no-print" style="padding: 14px; margin-bottom: 16px;">
    <form action="index.php" method="GET" style="display:flex; gap:10px; align-items:center; max-width:500px;">
        <input type="hidden" name="ruta" value="reportes/historial">
        <label for="cedula" style="font-weight:600; font-size:0.88rem; white-space:nowrap;">Cédula:</label>
        <input type="text" id="cedula" name="cedula" maxlength="10" class="campo-control input-solo-numeros" placeholder="Ej: 1712345678 (vacío = ver todo)" value="<?= htmlspecialchars($cedula ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-primario btn-sm">Buscar</button>
        <?php if (!empty($cedula)): ?>
            <a href="index.php?ruta=reportes/historial" class="btn btn-secundario btn-sm">Ver Todos</a>
        <?php endif; ?>
    </form>
</div>

<!-- Listado de consultas clínicas -->
<?php if (empty($historiales)): ?>
    <div class="tarjeta" style="text-align:center; padding:30px; color:#64748B;">
        No se encontraron registros de historial clínico con el criterio ingresado.
    </div>
<?php else: ?>
    <?php foreach ($historiales as $h): ?>
        <div class="tarjeta" style="margin-bottom:14px; border-left:4px solid var(--color-primario);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; border-bottom:1px solid #D6E2EC; padding-bottom:6px;">
                <div>
                    <strong style="color:var(--color-primario); font-size:1.05rem;"><?= htmlspecialchars($h['paciente'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span style="font-size:0.85rem; color:#64748B; margin-left:8px;">C.I. <?= htmlspecialchars($h['cedula'], ENT_QUOTES, 'UTF-8') ?> &bull; Tel: <?= htmlspecialchars($h['telefono'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div style="font-size:0.85rem; color:#64748B;">
                    Fecha: <strong><?= date('d/m/Y H:i', strtotime($h['fecha_cita'])) ?></strong>
                </div>
            </div>

            <div style="font-size:0.88rem; margin-bottom:6px;">
                <span style="color:#64748B; font-weight:600;">MÉDICO:</span> <?= htmlspecialchars($h['especialista'], ENT_QUOTES, 'UTF-8') ?> &bull; <em><?= htmlspecialchars($h['especialidad'], ENT_QUOTES, 'UTF-8') ?></em>
            </div>

            <div style="margin-bottom:6px;">
                <span style="color:#64748B; font-weight:600; font-size:0.85rem;">DIAGNÓSTICO CLÍNICO:</span>
                <div style="background:#F8FAFC; border:1px solid #D6E2EC; border-radius:6px; padding:8px 12px; font-weight:600; color:#0F172A; margin-top:2px;">
                    <?= nl2br(htmlspecialchars($h['diagnostico'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
            </div>

            <?php if (!empty($h['notas'])): ?>
                <div>
                    <span style="color:#64748B; font-weight:600; font-size:0.85rem;">NOTAS Y EVOLUCIÓN:</span>
                    <div style="font-style:italic; font-size:0.9rem; color:#0F172A; margin-top:2px;">
                        <?= nl2br(htmlspecialchars($h['notas'], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>

