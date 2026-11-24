<?php
/**
 * atencion.php
 * Para qué sirve: Ficha clínica de atención, historial previo, registro de diagnóstico y prescripción médica.
 * Trabaja con: controllers/MedicoController.php.
 * Equivale en NetBeans a: PanelHistorialMedico.java
 */
require_once __DIR__ . '/../header.php';
$diagnosticoPrevio = $historialActual['diagnostico'] ?? '';
$notasPrevias = $historialActual['notas'] ?? '';
$esFinalizada = ($cita['estado'] === 'Finalizada');

// Preparar texto de medicamentos si ya existe una receta previa
$lineasMedicamentos = [];
if (!empty($recetaActual['medicamentos'])) {
    foreach ($recetaActual['medicamentos'] as $med) {
        $lineasMedicamentos[] = $med['medicamento'];
    }
}
$prescripcionTexto = implode("\n", $lineasMedicamentos);
$indicacionesPrevias = $recetaActual['indicaciones'] ?? '';
?>

<!-- Encabezado con estado y acciones rápidas -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="color:var(--color-primario); margin:0 0 6px 0;">Atención Médica - Cita #<?= str_pad((string)$cita['id_cita'], 5, '0', STR_PAD_LEFT) ?></h2>
        <span class="badge badge-<?= strtolower(str_replace(' ', '-', $cita['estado'])) ?>">
            Estado: <?= htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="index.php?ruta=medico/agenda" class="btn btn-secundario">◀ Volver a Agenda</a>
        <?php if (!empty($recetaActual)): ?>
            <a href="index.php?ruta=medico/receta_imprimible&id=<?= (int)$recetaActual['id_receta'] ?>" target="_blank" class="btn btn-primario">
                🖨 Ver / Imprimir Receta (<?= htmlspecialchars($recetaActual['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>)
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($esFinalizada): ?>
    <!-- Alerta destacada de solo lectura para citas finalizadas -->
    <div style="background:#FFFBEB; border-left:4px solid #F59E0B; padding:14px 18px; border-radius:8px; margin-bottom:20px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size:1.6rem; line-height:1;">🔒</span>
        <div>
            <strong style="color:#92400E; font-size:1rem; display:block;">No se puede editar porque esta cita ya finalizó.</strong>
            <span style="color:#78350F; font-size:0.88rem;">La atención médica y la prescripción fueron completadas y se encuentran en modo de solo lectura para preservar la fidelidad del historial clínico.</span>
        </div>
    </div>
<?php endif; ?>

<div class="atencion-grilla">
    <!-- Columna Izquierda: Datos del paciente e historial clínico previo -->
    <div>
        <div class="tarjeta">
            <h3 style="color:var(--color-primario); font-size:1.15rem; margin-bottom:14px;">👤 Datos del Paciente</h3>
            
            <div style="background:#F8FAFC; border:1px solid #D6E2EC; border-radius:8px; padding:12px; margin-bottom:16px; font-size:0.9rem;">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($cita['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Cédula:</strong> <?= htmlspecialchars($cita['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($cita['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Fecha Consulta:</strong> <?= date('d/m/Y H:i', strtotime($cita['fecha_cita'])) ?></p>
            </div>

            <h4 style="color:var(--color-primario); font-size:1rem; margin-bottom:10px;">
                📁 Historial Previo (<?= count($consultasAnteriores) ?> consultas)
            </h4>

            <?php if (empty($consultasAnteriores)): ?>
                <p style="color:#64748B; font-size:0.85rem;">No registra atenciones previas en el consultorio.</p>
            <?php else: ?>
                <div style="max-height: 380px; overflow-y:auto; padding-right:4px;">
                    <?php foreach ($consultasAnteriores as $ant): ?>
                        <div style="background:#FFFFFF; border:1px solid #D6E2EC; border-radius:8px; padding:10px; margin-bottom:10px; font-size:0.88rem;">
                            <div style="display:flex; justify-content:space-between; color:#64748B; font-size:0.8rem; margin-bottom:4px;">
                                <span><?= date('d/m/Y', strtotime($ant['fecha_cita'])) ?></span>
                                <span><?= htmlspecialchars($ant['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div style="font-weight:600; color:var(--color-primario);">
                                Diagnóstico: <?= htmlspecialchars($ant['diagnostico'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <?php if (!empty($ant['notas'])): ?>
                                <div style="color:#0F172A; margin-top:4px;">
                                    <em>Notas: <?= htmlspecialchars($ant['notas'], ENT_QUOTES, 'UTF-8') ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Diagnóstico y Prescripción Integrada -->
    <div>
        <div class="tarjeta">
            <h3 style="color:var(--color-primario); font-size:1.15rem; margin-bottom:14px;">🩺 Registro de la Consulta y Prescripción</h3>

            <form action="index.php?ruta=medico/atencion&id=<?= $cita['id_cita'] ?>" method="POST" onsubmit="return confirm('¿Desea registrar el diagnóstico y finalizar la atención de esta cita?');">
                <!-- Diagnóstico Clínico -->
                <div class="campo-grupo">
                    <label for="diagnostico" class="campo-etiqueta">Diagnóstico Clínico *</label>
                    <textarea name="diagnostico" id="diagnostico" rows="3" class="campo-control" placeholder="Escriba aquí el diagnóstico clínico del paciente (obligatorio)..." required <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($diagnosticoPrevio, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Observaciones y Notas de Evolución -->
                <div class="campo-grupo">
                    <label for="notas" class="campo-etiqueta">Observaciones y Notas de Evolución</label>
                    <textarea name="notas" id="notas" rows="3" class="campo-control" placeholder="Signos vitales, recomendaciones, antecedentes relevantes..." <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($notasPrevias, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Sección Integrada: Prescripción de Medicamentos -->
                <div id="seccion-receta" style="margin-top:20px; padding:16px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <h4 style="margin:0; color:#166534; font-size:1rem; display:flex; align-items:center; gap:6px;">
                            💊 Prescripción de Medicamentos (Envío automático al paciente)
                        </h4>
                        <?php if (!empty($recetaActual)): ?>
                            <span style="font-size:0.8rem; background:#DCFCE7; color:#15803D; padding:3px 8px; border-radius:12px; font-weight:600;">
                                Código: <?= htmlspecialchars($recetaActual['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <p style="color:#15803D; font-size:0.83rem; margin-bottom:10px;">
                        Escriba los medicamentos y posología en texto libre. Cada línea será registrada y estará disponible al instante en el portal del paciente (Mis Recetas) para consulta e impresión.
                    </p>

                    <div class="campo-grupo" style="margin-bottom:12px;">
                        <label for="prescripcion" class="campo-etiqueta" style="color:#166534;">Medicamentos y Dosis (uno por línea o en texto libre)</label>
                        <textarea name="prescripcion" id="prescripcion" rows="4" class="campo-control" style="background:#FFFFFF; font-family:monospace, sans-serif; font-size:0.9rem;" placeholder="Ejemplo:
- Tomar Finalin cada 12 horas por 3 días
- Amoxicilina 500mg cada 8 horas después de comer por 7 días
- Suero oral 1 litro al día" <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($prescripcionTexto, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="campo-grupo" style="margin-bottom:4px;">
                        <label for="indicaciones" class="campo-etiqueta" style="color:#166534;">Indicaciones Generales, Dieta o Cuidados</label>
                        <textarea name="indicaciones" id="indicaciones" rows="2" class="campo-control" style="background:#FFFFFF;" placeholder="Ejemplo: Reposo relativo por 48 horas, hidratación abundante y evitar grasas..." <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($indicacionesPrevias, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <!-- Botones y Acciones -->
                <div style="margin-top:24px; padding-top:14px; border-top:1px solid #E2E8F0;">
                    <?php if ($esFinalizada): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <span style="color:#64748B; font-size:0.9rem;">
                                🔒 Cita atendida y finalizada. Los datos se encuentran en modo de solo lectura.
                            </span>
                            <?php if (!empty($recetaActual)): ?>
                                <a href="index.php?ruta=medico/receta_imprimible&id=<?= (int)$recetaActual['id_receta'] ?>" target="_blank" class="btn btn-primario">
                                    🖨 Imprimir Receta Médica
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
                            <button type="submit" class="btn btn-exito" style="padding:10px 22px; font-size:0.95rem; font-weight:600;">
                                ✔ Finalizar y Guardar Atención Médica
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

