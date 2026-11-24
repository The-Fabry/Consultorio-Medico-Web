<?php
/**
 * mis_citas.php
 * Para qué sirve: Muestra la tabla de citas del paciente con insignias de color y opción de cancelación.
 * Trabaja con: controllers/PacienteController.php.
 * Equivale en NetBeans a: JTable en VistaPaciente.java
 */
require_once __DIR__ . '/../header.php';
?>

<div class="tarjeta">
    <div class="tarjeta-cabecera" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>📋 Mis Citas Médicas</h2>
        <a href="index.php?ruta=paciente/agendar" class="btn btn-primario btn-sm">+ Agendar Cita</a>
    </div>

    <?php if (empty($citas)): ?>
        <p style="text-align:center; color:#64748B; padding:30px;">Aún no tiene citas agendadas.</p>
    <?php else: ?>
        <div class="tabla-responsive">
            <table class="tabla-clinica">
                <thead>
                    <tr>
                        <th>N.º Cita</th>
                        <th>Fecha y Hora</th>
                        <th>Médico Especialista</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                        <?php 
                            $esPendiente = ($c['estado'] === 'Pendiente');
                            $esFutura = (strtotime($c['fecha_cita']) >= time());
                            $puedeCancelar = $esPendiente && $esFutura;
                        ?>
                        <tr>
                            <td><strong>#<?= str_pad((string)$c['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_cita'])) ?></td>
                            <td><?= htmlspecialchars($c['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['especialidad'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $c['estado'])) ?>">
                                    <?= htmlspecialchars($c['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <a href="index.php?ruta=paciente/ticket&id=<?= $c['id_cita'] ?>" class="btn btn-secundario btn-sm">
                                    🎫 Ticket
                                </a>

                                <?php if ($puedeCancelar): ?>
                                    <!-- Botón para cancelar con confirmación estándar de JavaScript -->
                                    <form action="index.php?ruta=paciente/cancelar" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de cancelar esta cita médica? Esta acción liberará el horario del médico.');">
                                        <input type="hidden" name="id_cita" value="<?= $c['id_cita'] ?>">
                                        <button type="submit" class="btn btn-peligro btn-sm">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secundario btn-sm" disabled title="No cancelable en estado <?= $c['estado'] ?>">
                                        Cancelar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>

