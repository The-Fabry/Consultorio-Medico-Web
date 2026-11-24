<?php
/**
 * atencion.php
 * Para qué sirve: Ficha clínica de atención, historial previo, registro de diagnóstico y prescripción médica.
 * Trabaja con: controllers/MedicoController.php.
 * Equivale en NetBeans a: PanelHistorialMedico.java
 * 
 * Estructura visual: Contenedor centrado en espacio medio (max-width: 980px)
 * con dos columnas equilibradas:
 *   - Columna 1: Información del paciente e historial clínico previo.
 *   - Columna 2: Registro del diagnóstico, notas de evolución y prescripción farmacológica integrada.
 */
require_once __DIR__ . '/../header.php';

// Variables de apoyo preparadas para la vista
$diagnosticoPrevio = $historialActual['diagnostico'] ?? '';
$notasPrevias = $historialActual['notas'] ?? '';
$esFinalizada = ($cita['estado'] === 'Finalizada');

// Si la cita ya tiene una receta emitida, preparamos sus medicamentos para mostrarlos en texto libre
$lineasMedicamentos = [];
if (!empty($recetaActual['medicamentos'])) {
    foreach ($recetaActual['medicamentos'] as $med) {
        $lineasMedicamentos[] = $med['medicamento'];
    }
}
$prescripcionTexto = implode("\n", $lineasMedicamentos);
$indicacionesPrevias = $recetaActual['indicaciones'] ?? '';
?>

<!-- Contenedor centrado en espacio medio para una experiencia visual equilibrada -->
<div class="contenedor-atencion">

    <!-- Encabezado de la atención médica con estado y botones de navegación -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <h2 style="color:var(--color-primario); margin:0; font-size:1.45rem;">
                    Atención Médica - Cita #<?= str_pad((string)$cita['id_cita'], 5, '0', STR_PAD_LEFT) ?>
                </h2>
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $cita['estado'])) ?>">
                    <?= htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <p style="margin:4px 0 0 0; color:var(--color-texto-muted); font-size:0.88rem;">
                Registro clínico de consulta y emisión directa de receta farmacológica.
            </p>
        </div>

        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="index.php?ruta=medico/agenda" class="btn btn-secundario">
                ◀ Volver a Agenda
            </a>
            <?php if (!empty($recetaActual)): ?>
                <a href="index.php?ruta=medico/receta_imprimible&id=<?= (int)$recetaActual['id_receta'] ?>" target="_blank" class="btn btn-primario">
                    🖨 Ver / Imprimir Receta (<?= htmlspecialchars($recetaActual['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($esFinalizada): ?>
        <!-- Alerta destacada cuando la cita ya fue finalizada (Modo de solo lectura) -->
        <div style="background:#FFFBEB; border-left:4px solid #F59E0B; padding:14px 18px; border-radius:10px; margin-bottom:22px; display:flex; align-items:center; gap:14px; box-shadow:0 2px 8px rgba(245, 158, 11, 0.08);">
            <span style="font-size:1.75rem; line-height:1;">🔒</span>
            <div>
                <strong style="color:#92400E; font-size:1.02rem; display:block; margin-bottom:2px;">
                    No se puede editar porque esta cita ya finalizó.
                </strong>
                <span style="color:#78350F; font-size:0.88rem; line-height:1.45; display:block;">
                    La consulta fue completada satisfactoriamente. Los datos clínicos y la prescripción se mantienen en modo de solo lectura para asegurar la integridad del expediente médico.
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grilla principal de dos columnas equilibradas -->
    <div class="atencion-grilla">

        <!-- Columna 1: Información del paciente y consultas previas -->
        <div>
            <div class="tarjeta-atencion">
                <div class="atencion-tarjeta-titulo">
                    <span>👤</span>
                    <span>Datos del Paciente</span>
                </div>
                
                <div class="paciente-ficha">
                    <p style="margin:0 0 6px 0;"><strong>Nombre:</strong> <?= htmlspecialchars($cita['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0 0 6px 0;"><strong>Cédula:</strong> <?= htmlspecialchars($cita['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0 0 6px 0;"><strong>Teléfono:</strong> <?= htmlspecialchars($cita['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0;"><strong>Fecha de Consulta:</strong> <?= date('d/m/Y H:i', strtotime($cita['fecha_cita'])) ?></p>
                </div>

                <div class="atencion-tarjeta-titulo" style="font-size:1rem; margin-top:20px;">
                    <span>📁</span>
                    <span>Historial Previo (<?= count($consultasAnteriores) ?> consultas)</span>
                </div>

                <?php if (empty($consultasAnteriores)): ?>
                    <p style="color:#64748B; font-size:0.86rem; margin:10px 0 0 0; text-align:center; padding:16px; background:#F8FAFC; border-radius:8px;">
                        No registra atenciones previas en este consultorio.
                    </p>
                <?php else: ?>
                    <div style="max-height: 380px; overflow-y:auto; padding-right:4px;">
                        <?php foreach ($consultasAnteriores as $ant): ?>
                            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:10px; font-size:0.88rem; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                                <div style="display:flex; justify-content:space-between; color:#64748B; font-size:0.78rem; margin-bottom:4px;">
                                    <span>📅 <?= date('d/m/Y', strtotime($ant['fecha_cita'])) ?></span>
                                    <span>👨‍⚕️ <?= htmlspecialchars($ant['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div style="font-weight:600; color:var(--color-primario); margin-top:2px;">
                                    Diagnóstico: <?= htmlspecialchars($ant['diagnostico'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php if (!empty($ant['notas'])): ?>
                                    <div style="color:#475569; margin-top:4px; font-size:0.84rem;">
                                        <em>Notas: <?= htmlspecialchars($ant['notas'], ENT_QUOTES, 'UTF-8') ?></em>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna 2: Formulario Clínico y Prescripción Farmacológica Integrada -->
        <div>
            <div class="tarjeta-atencion">
                <div class="atencion-tarjeta-titulo">
                    <span>🩺</span>
                    <span>Registro Clínico y Prescripción</span>
                </div>

                <form action="index.php?ruta=medico/atencion&id=<?= $cita['id_cita'] ?>" method="POST" onsubmit="return confirm('¿Desea registrar el diagnóstico y finalizar la atención médica de esta cita?');">
                    <!-- Campo obligatorio: Diagnóstico Clínico -->
                    <div class="campo-grupo">
                        <label for="diagnostico" class="campo-etiqueta">
                            Diagnóstico Clínico * <span style="font-weight:normal; font-size:0.8rem; color:#64748B;">(Obligatorio)</span>
                        </label>
                        <textarea name="diagnostico" id="diagnostico" rows="3" class="campo-control" placeholder="Escriba aquí el diagnóstico clínico del paciente..." required <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($diagnosticoPrevio, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Campo opcional: Notas y Observaciones de evolución -->
                    <div class="campo-grupo" style="margin-top:14px;">
                        <label for="notas" class="campo-etiqueta">
                            Observaciones y Notas de Evolución <span style="font-weight:normal; font-size:0.8rem; color:#64748B;">(Opcional)</span>
                        </label>
                        <textarea name="notas" id="notas" rows="3" class="campo-control" placeholder="Signos vitales, recomendaciones, antecedentes relevantes..." <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($notasPrevias, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Sección Integrada: Prescripción de Medicamentos en Texto Libre -->
                    <div id="seccion-receta" class="bloque-prescripcion">
                        <div class="bloque-prescripcion-cabecera">
                            <h4 class="bloque-prescripcion-titulo">
                                <span>💊</span>
                                <span>Prescripción de Medicamentos</span>
                            </h4>
                            <?php if (!empty($recetaActual)): ?>
                                <span style="font-size:0.8rem; background:#EFF6FF; color:var(--color-primario); padding:3px 10px; border-radius:12px; font-weight:600; border:1px solid #BFDBFE;">
                                    Código: <?= htmlspecialchars($recetaActual['codigo_receta'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="campo-grupo" style="margin-bottom:14px;">
                            <label for="prescripcion" class="campo-etiqueta">
                                Medicamentos y Posología <span style="font-weight:normal; font-size:0.8rem; color:#64748B;">(uno por línea o en texto libre)</span>
                            </label>
                            <textarea name="prescripcion" id="prescripcion" rows="4" class="campo-control prescripcion-textarea" placeholder="Ejemplo:
- Tomar Finalin cada 12 horas por 3 días
- Amoxicilina 500mg cada 8 horas después de comer por 7 días
- Suero oral 1 litro al día" <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($prescripcionTexto, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="campo-grupo" style="margin-bottom:0;">
                            <label for="indicaciones" class="campo-etiqueta">
                                Indicaciones Generales, Dieta o Cuidados Especiales
                            </label>
                            <textarea name="indicaciones" id="indicaciones" rows="2" class="campo-control" style="background:#FFFFFF;" placeholder="Ejemplo: Reposo relativo por 48 horas, hidratación abundante y dieta blanda..." <?= $esFinalizada ? 'disabled' : '' ?>><?= htmlspecialchars($indicacionesPrevias, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <!-- Barra de Acciones y Guardado Final -->
                    <div class="acciones-atencion">
                        <?php if ($esFinalizada): ?>
                            <span style="color:#64748B; font-size:0.9rem; display:flex; align-items:center; gap:6px;">
                                <span>🔒</span> Consulta completada (Modo solo lectura).
                            </span>
                            <?php if (!empty($recetaActual)): ?>
                                <a href="index.php?ruta=medico/receta_imprimible&id=<?= (int)$recetaActual['id_receta'] ?>" target="_blank" class="btn btn-primario">
                                    🖨 Imprimir Receta Médica
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#64748B; font-size:0.83rem;">
                                Al guardar, la cita se marcará como <strong>Finalizada</strong> y la receta estará lista para el paciente.
                            </span>
                            <button type="submit" class="btn btn-exito" style="padding:10px 24px; font-size:0.95rem; font-weight:700; box-shadow:0 2px 8px rgba(16, 185, 129, 0.25);">
                                ✔ Finalizar y Guardar Atención
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

