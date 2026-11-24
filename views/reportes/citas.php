<?php
/**
 * citas.php
 * Para qué sirve: Reporte unificado de citas médicas (general o por rango de fechas) listo para imprimir.
 * Trabaja con: controllers/ReporteController.php y views/reportes/cabecera_reporte.php.
 * Equivale en NetBeans a: GestorReportes.java / Reportes JasperReports
 */
require_once __DIR__ . '/../header.php';
$esRango = (!empty($fechaInicio) && !empty($fechaFin));

// Preparar datos para la cabecera unificada
$tituloReporte = "SMARTMED - REPORTE DE CITAS";
$subtituloReporte = "Control y auditoría de citas médicas, especialidades y estados de atención";
$contextoPartes = [];
if (!empty($especialistaActual)) {
    $contextoPartes[] = "Médico: " . htmlspecialchars($especialistaActual['nombre_completo'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($especialistaActual['especialidad'], ENT_QUOTES, 'UTF-8') . ")";
} else {
    $contextoPartes[] = "Vista General del Consultorio (Recepción/Administración)";
}
if ($esRango) {
    $contextoPartes[] = "Período: " . date('d/m/Y', strtotime($fechaInicio)) . " al " . date('d/m/Y', strtotime($fechaFin));
} else {
    $contextoPartes[] = "Histórico Completo";
}
$contextoPartes[] = "Total Registros: " . count($citas);
$contextoReporte = implode(" &bull; ", $contextoPartes);

// Renderizar cabecera superior unificada
require __DIR__ . '/cabecera_reporte.php';
// URL para descargar o visualizar en PDF con Dompdf
$urlPdf = "index.php?ruta=reportes/pdf&tipo=citas" . 
          (!empty($fechaInicio) ? "&fecha_inicio=" . urlencode($fechaInicio) : "") . 
          (!empty($fechaFin) ? "&fecha_fin=" . urlencode($fechaFin) : "");
?>

<!-- Barra de acciones rápidas (oculta al imprimir) -->
<div class="no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; flex-wrap:wrap; gap:10px;">
    <span style="color:#64748B; font-size:0.88rem;">Utilice los filtros para segmentar fechas o exporte el reporte oficial en PDF.</span>
    <div style="display:flex; gap:10px;">
        <a href="<?= $urlPdf ?>" target="_blank" class="btn btn-acento">
            📄 Generar PDF (Dompdf)
        </a>
        <button type="button" class="btn btn-primario" onclick="window.print()">
            🖨 Imprimir Reporte
        </button>
    </div>
</div>

<!-- Filtro por rango de fechas (oculto al imprimir) -->
<div class="tarjeta no-print" style="padding: 14px; margin-bottom: 16px;">
    <form action="index.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
        <input type="hidden" name="ruta" value="reportes/citas">

        <div style="display:flex; align-items:center; gap:6px;">
            <label for="fecha_inicio" style="font-weight:600; font-size:0.88rem;">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" class="campo-control" value="<?= htmlspecialchars($fechaInicio, ENT_QUOTES, 'UTF-8') ?>" style="width:auto;">
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <label for="fecha_fin" style="font-weight:600; font-size:0.88rem;">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" class="campo-control" value="<?= htmlspecialchars($fechaFin, ENT_QUOTES, 'UTF-8') ?>" style="width:auto;">
        </div>

        <button type="submit" class="btn btn-primario btn-sm">Filtrar Rango</button>
        <?php if ($esRango): ?>
            <a href="index.php?ruta=reportes/citas" class="btn btn-secundario btn-sm">Ver Todas</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de resultados -->
<div class="tarjeta" style="padding:0; overflow:hidden;">
    <div class="tabla-responsive">
        <table class="tabla-clinica">
            <thead>
                <tr>
                    <th>N.º Cita</th>
                    <th>Fecha y Hora</th>
                    <th>Ticket</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Especialista</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:#64748B;">No hay citas médicas registradas para este criterio.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $c): ?>
                        <tr>
                            <td><strong>#<?= str_pad((string)$c['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_cita'])) ?></td>
                            <td><code><?= htmlspecialchars($c['codigo_barras'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($c['paciente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['cedula'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['especialista'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['especialidad'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $c['estado'])) ?>">
                                    <?= htmlspecialchars($c['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>

