<?php
/**
 * atencion.php
 * Para qué sirve: Ficha clínica de atención, historial previo y registro de diagnóstico.
 * Trabaja con: controllers/MedicoController.php.
 * Equivale en NetBeans a: PanelHistorialMedico.java
 */
require_once __DIR__ . '/../header.php';
$diagnosticoPrevio = $historialActual['diagnostico'] ?? '';
$notasPrevias = $historialActual['notas'] ?? '';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
    <div>
        <h2 style="color:var(--color-primario);">Atención Médica - Cita #<?= str_pad((string)$cita['id_cita'], 5, '0', STR_PAD_LEFT) ?></h2>
        <span class="badge badge-<?= strtolower(str_replace(' ', '-', $cita['estado'])) ?>">
            Estado: <?= htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="index.php?ruta=medico/agenda" class="btn btn-secundario">◀ Volver a Agenda</a>
        <a href="index.php?ruta=medico/receta&id_cita=<?= $cita['id_cita'] ?>" class="btn btn-acento">💊 Generar Receta</a>
    </div>
</div>

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
                <div style="max-height: 350px; overflow-y:auto; padding-right:4px;">
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

    <!-- Columna Derecha: Formulario de Diagnóstico y Atención -->
    <div>
        <div class="tarjeta">
            <h3 style="color:var(--color-primario); font-size:1.15rem; margin-bottom:14px;">🩺 Registro de la Consulta</h3>

            <form action="index.php?ruta=medico/atencion&id=<?= $cita['id_cita'] ?>" method="POST" onsubmit="return confirm('¿Desea registrar el diagnóstico y finalizar la atención de esta cita?');">
                <div class="campo-grupo">
                    <label for="diagnostico" class="campo-etiqueta">Diagnóstico Clínico *</label>
                    <textarea name="diagnostico" id="diagnostico" rows="4" class="campo-control" placeholder="Escriba aquí el diagnóstico clínico del paciente (obligatorio)..." required><?= htmlspecialchars($diagnosticoPrevio, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="campo-grupo">
                    <label for="notas" class="campo-etiqueta">Observaciones y Notas de Evolución</label>
                    <textarea name="notas" id="notas" rows="4" class="campo-control" placeholder="Signos vitales, recomendaciones, notas adicionales..."><?= htmlspecialchars($notasPrevias, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                    <a href="index.php?ruta=medico/receta&id_cita=<?= $cita['id_cita'] ?>" class="btn btn-secundario">
                        💊 Prescribir Medicamentos
                    </a>
                    <button type="submit" class="btn btn-exito">
                        ✔ Finalizar y Guardar Atención
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

