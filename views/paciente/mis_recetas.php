<?php
/**
 * mis_recetas.php
 * Para qué sirve: Muestra las recetas electrónicas emitidas para el paciente actual.
 * Trabaja con: controllers/PacienteController.php y views/medico/receta_imprimible.php.
 * Equivale en NetBeans a: No existía en la versión de escritorio (NUEVA función web).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="tarjeta">
    <div class="tarjeta-cabecera">
        <h2>💊 Mis Recetas Médicas</h2>
    </div>

    <?php if (empty($recetas)): ?>
        <p style="text-align:center; color:#64748B; padding:30px;">Aún no le han emitido recetas médicas.</p>
    <?php else: ?>
        <div class="tabla-responsive">
            <table class="tabla-clinica">
                <thead>
                    <tr>
                        <th>Código Receta</th>
                        <th>Fecha de Emisión</th>
                        <th>Médico Tratante</th>
                        <th>Especialidad</th>
                        <th>Fecha de Cita</th>
                        <th style="text-align:center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['codigo_receta'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['fecha_emision'])) ?></td>
                            <td><?= htmlspecialchars($r['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($r['especialidad'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('d/m/Y', strtotime($r['fecha_cita'])) ?></td>
                            <td style="text-align:center;">
                                <a href="index.php?ruta=medico/receta_imprimible&id=<?= $r['id_receta'] ?>" target="_blank" class="btn btn-secundario btn-sm">
                                    🖨 Ver / Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>

