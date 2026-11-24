<?php
/**
 * cabecera_reporte.php
 * Para qué sirve: Cabecera superior unificada, limpia y profesional para reportes e historiales clínicos.
 * Parámetros esperados:
 * - $tituloReporte: Nombre oficial del reporte (ej. "SMARTMED - REPORTE DE CITAS")
 * - $subtituloReporte: Explicación breve (ej. "Control y auditoría de citas médicas, especialidades y estados de atención")
 * - $contextoReporte: Alcance o filtro activo (ej. médico en sesión, fechas o datos del paciente)
 * - $codigoEmision: (Opcional) Código identificador
 */

$fechaEmisionActual = date('d/m/Y H:i');
?>
<div class="cabecera-reporte-unificada">
    <div class="cabecera-reporte-grid">
        <!-- Lado Izquierdo: Identidad de la Clínica y Título Oficial del Reporte -->
        <div class="cabecera-reporte-info">
            <div class="cabecera-reporte-marca">
                <span class="cabecera-reporte-logo-simbolo">✚</span>
                <span class="cabecera-reporte-marca-nombre">SMARTMED CONSULTORIO</span>
            </div>
            <h1 class="cabecera-reporte-titulo"><?= htmlspecialchars($tituloReporte ?? 'SMARTMED - REPORTE CLÍNICO', ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="cabecera-reporte-subtitulo"><?= htmlspecialchars($subtituloReporte ?? 'Sistema Integral de Gestión Médica', ENT_QUOTES, 'UTF-8') ?></p>
            
            <?php if (!empty($contextoReporte)): ?>
                <div class="cabecera-reporte-contexto">
                    <strong>Alcance:</strong> <?= $contextoReporte ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lado Derecho: Metadatos de Emisión -->
        <div class="cabecera-reporte-meta">
            <div class="cabecera-reporte-caja-meta">
                <div class="cabecera-reporte-fila-meta">
                    <span class="cabecera-reporte-lbl">FECHA DE EMISIÓN:</span>
                    <strong class="cabecera-reporte-val"><?= $fechaEmisionActual ?></strong>
                </div>
                <?php if (!empty($codigoEmision)): ?>
                    <div class="cabecera-reporte-fila-meta" style="margin-top: 5px;">
                        <span class="cabecera-reporte-lbl">CÓDIGO:</span>
                        <code class="cabecera-reporte-codigo"><?= htmlspecialchars($codigoEmision, ENT_QUOTES, 'UTF-8') ?></code>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
