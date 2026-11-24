<?php
/**
 * cabecera_reporte.php
 * Para qué sirve: Componente reutilizable que renderiza la cabecera superior unificada
 *                 para reportes, comprobantes, recetas e historiales clínicos.
 * Parámetros esperados:
 * - $tituloReporte: Nombre oficial del reporte (ej. "SMARTMED - HISTORIAL CLÍNICO")
 * - $subtituloReporte: Explicación breve (ej. "Expediente médico de consultas, diagnósticos y notas de evolución")
 * - $contextoReporte: Alcance o filtro activo (ej. paciente específico, médico en sesión, período de fechas)
 * - $codigoEmision: (Opcional) Código identificador o comprobante
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
                <span class="cabecera-reporte-tag">DOCUMENTO OFICIAL</span>
            </div>
            <h1 class="cabecera-reporte-titulo"><?= htmlspecialchars($tituloReporte ?? 'SMARTMED - REPORTE CLÍNICO', ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="cabecera-reporte-subtitulo"><?= htmlspecialchars($subtituloReporte ?? 'Sistema Integral de Gestión Médica', ENT_QUOTES, 'UTF-8') ?></p>
            
            <?php if (!empty($contextoReporte)): ?>
                <div class="cabecera-reporte-contexto">
                    <strong>Alcance:</strong> <?= $contextoReporte ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lado Derecho: Metadatos de Emisión, Fecha y Código -->
        <div class="cabecera-reporte-meta">
            <div class="cabecera-reporte-caja-meta">
                <div class="cabecera-reporte-fila-meta">
                    <span class="cabecera-reporte-lbl">FECHA DE EMISIÓN:</span>
                    <strong class="cabecera-reporte-val"><?= $fechaEmisionActual ?></strong>
                </div>
                <?php if (!empty($codigoEmision)): ?>
                    <div class="cabecera-reporte-fila-meta" style="margin-top: 6px;">
                        <span class="cabecera-reporte-lbl">CÓDIGO DE REGISTRO:</span>
                        <code class="cabecera-reporte-codigo"><?= htmlspecialchars($codigoEmision, ENT_QUOTES, 'UTF-8') ?></code>
                    </div>
                <?php endif; ?>
                <div class="cabecera-reporte-seguridad">
                    <span>Certificación Digital SmartMed Web</span>
                </div>
            </div>
        </div>
    </div>
</div>
