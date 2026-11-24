<?php
/**
 * receta_imprimible.php
 * Para qué sirve: Hoja oficial e imprimible de receta médica electrónica con firma y código de barras.
 * Trabaja con: controllers/MedicoController.php, views/reportes/cabecera_reporte.php y assets/js/medico.js.
 * Equivale en NetBeans a: Plantilla de reporte de recetas (NUEVA función web).
 */
require_once __DIR__ . '/../header.php';

// Preparar datos para la cabecera unificada
$tituloReporte = "SMARTMED - RECETAS MÉDICAS";
$subtituloReporte = "Prescripción farmacológica oficial, dosificación y recomendaciones terapéuticas";
$contextoReporte = "Paciente: <strong>" . htmlspecialchars($receta['nombre_cliente'], ENT_QUOTES, 'UTF-8') . "</strong> (C.I. " . htmlspecialchars($receta['cedula_cliente'], ENT_QUOTES, 'UTF-8') . ") &bull; Médico: <strong>" . htmlspecialchars($receta['nombre_especialista'], ENT_QUOTES, 'UTF-8') . "</strong> (" . htmlspecialchars($receta['especialidad'], ENT_QUOTES, 'UTF-8') . ")";
$codigoEmision = $receta['codigo_receta'];

// Determinamos la ruta de retorno según el perfil del usuario autenticado
$usuarioSesion = usuarioActual();
$urlRetorno = 'index.php';
if ($usuarioSesion) {
    if ($usuarioSesion['rol'] === 'paciente') {
        $urlRetorno = 'index.php?ruta=paciente/recetas';
    } elseif ($usuarioSesion['rol'] === 'medico') {
        $urlRetorno = 'index.php?ruta=medico/agenda';
    } elseif ($usuarioSesion['rol'] === 'recepcion') {
        $urlRetorno = 'index.php?ruta=recepcion/citas';
    }
}
?>

<!-- Botones de navegación (ocultos al imprimir) -->
<div class="no-print" style="max-width:760px; margin:0 auto 16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="<?= $urlRetorno ?>" class="btn btn-secundario btn-sm">◀ Volver</a>
    <button type="button" class="btn btn-primario btn-sm" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
</div>

<!-- Hoja de Receta Médica -->
<div class="hoja-receta">
    <!-- Cabecera Superior Unificada -->
    <?php require __DIR__ . '/../reportes/cabecera_reporte.php'; ?>

    <!-- Datos del Paciente y Médico -->
    <div class="receta-paciente-banner">
        <div>
            <span style="font-size:0.75rem; color:#64748B;">PACIENTE:</span>
            <div><strong><?= htmlspecialchars($receta['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div style="font-size:0.85rem;">C.I. <?= htmlspecialchars($receta['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
            <span style="font-size:0.75rem; color:#64748B;">MÉDICO PRESCRIPTOR:</span>
            <div><strong><?= htmlspecialchars($receta['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div style="font-size:0.85rem;"><?= htmlspecialchars($receta['especialidad'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
            <span style="font-size:0.75rem; color:#64748B;">FECHA EMISIÓN:</span>
            <div><strong><?= date('d/m/Y H:i', strtotime($receta['fecha_emision'])) ?></strong></div>
            <div style="font-size:0.85rem;">Consulta #<?= str_pad((string)$receta['id_cita'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>
    </div>

    <!-- Tabla de Medicamentos -->
    <h3 style="font-size:1.05rem; color:var(--color-primario); margin-bottom:8px;">Prescripción Farmacológica (Rp.)</h3>
    <table class="tabla-clinica" style="margin-bottom:20px;">
        <thead>
            <tr>
                <th>Medicamento</th>
                <th>Dosis</th>
                <th>Frecuencia</th>
                <th>Duración</th>
                <th>Vía</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receta['medicamentos'] as $med): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($med['medicamento'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($med['dosis'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($med['frecuencia'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($med['duracion'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($med['via_administracion'] ?? 'Oral', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Indicaciones Generales -->
    <?php if (!empty($receta['indicaciones'])): ?>
        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.9rem; color:var(--color-primario); margin-bottom:4px;">Indicaciones y Recomendaciones:</h4>
            <div style="background:#F8FAFC; border:1px solid #D6E2EC; padding:10px 14px; border-radius:6px; font-size:0.9rem; line-height:1.5;">
                <?= nl2br(htmlspecialchars($receta['indicaciones'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Firma y Código de Barras -->
    <div class="receta-firma-seccion">
        <div>
            <svg id="barcode-receta" data-codigo="<?= htmlspecialchars($receta['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>" style="max-height:55px;"></svg>
            <div style="font-family: 'Courier New', Courier, monospace; font-size: 0.95rem; font-weight: 700; color: #0F172A; text-align: center; letter-spacing: 1px; margin-top: 3px;">
                <?= htmlspecialchars($receta['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div style="font-size:0.75rem; color:#64748B; text-align: center; margin-top: 2px;">Validación digital farmacéutica</div>
        </div>
        <div class="receta-firma-linea">
            Firma y Sello del Médico<br>
            <span style="font-size:0.8rem; font-weight:normal; color:#64748B;">
                <?= htmlspecialchars($receta['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>
</div>

<script src="assets/js/JsBarcode.all.min.js"></script>
<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

