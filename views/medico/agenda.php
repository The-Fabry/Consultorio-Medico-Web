<?php
/**
 * agenda.php
 * Para qué sirve: Muestra las citas asignadas al médico especialista logueado.
 * Trabaja con: controllers/MedicoController.php y assets/js/medico.js.
 * Equivale en NetBeans a: VistaMedico.java
 */
require_once __DIR__ . '/../header.php';
?>

<div class="cabecera-seccion-flex">
    <div>
        <h2 style="color:var(--color-primario); margin:0 0 4px 0;">Mi Agenda Médica</h2>
        <p style="color:#64748B; font-size:0.9rem; margin:0;">
            <?php if (!empty($especialista)): ?>
                Médico en sesión: <strong><?= htmlspecialchars($especialista['nombre_completo'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($especialista['especialidad'], ENT_QUOTES, 'UTF-8') ?>) &bull; 
            <?php endif; ?>
            Mostrando únicamente las citas asignadas a su consulta.
        </p>
    </div>
    <a href="index.php?ruta=medico/agenda" class="btn btn-secundario">🔄 Actualizar Agenda</a>
</div>

<!-- Filtros de búsqueda (ocultos al imprimir) -->
<div class="tarjeta no-print" style="padding: 14px; margin-bottom: 16px;">
    <form action="index.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
        <input type="hidden" name="ruta" value="medico/agenda">

        <div style="display:flex; align-items:center; gap:6px;">
            <label for="fecha" style="font-weight:600; font-size:0.88rem;">Fecha:</label>
            <input type="date" id="fecha" name="fecha" class="campo-control" value="<?= htmlspecialchars($fechaFiltro, ENT_QUOTES, 'UTF-8') ?>" style="width:auto;">
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <label for="estado" style="font-weight:600; font-size:0.88rem;">Estado:</label>
            <select id="estado" name="estado" class="campo-control" style="width:auto;">
                <option value="">Todos los estados</option>
                <option value="Pendiente" <?= $estadoFiltro === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="En Proceso" <?= $estadoFiltro === 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                <option value="Finalizada" <?= $estadoFiltro === 'Finalizada' ? 'selected' : '' ?>>Finalizada</option>
                <option value="Cancelada" <?= $estadoFiltro === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primario btn-sm">Filtrar</button>
        <?php if ($fechaFiltro || $estadoFiltro): ?>
            <a href="index.php?ruta=medico/agenda" class="btn btn-secundario btn-sm">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de citas -->
<div class="tarjeta" style="padding:0; overflow:hidden;">
    <div class="tabla-responsive">
        <table class="tabla-clinica">
            <thead>
                <tr>
                    <th>N.º Cita</th>
                    <th>Fecha y Hora</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th style="text-align:center;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px; color:#64748B;">No tiene citas registradas con estos filtros.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $c): ?>
                        <?php 
                            $esEnProceso = ($c['estado'] === 'En Proceso');
                            $esFinalizada = ($c['estado'] === 'Finalizada');
                        ?>
                        <tr class="fila-agenda-medico" style="cursor:pointer;" title="Doble clic para abrir la cita">
                            <td><strong>#<?= str_pad((string)$c['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_cita'])) ?></td>
                            <td><?= htmlspecialchars($c['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $c['estado'])) ?>">
                                    <?= htmlspecialchars($c['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($esEnProceso): ?>
                                    <a href="index.php?ruta=medico/atencion&id=<?= $c['id_cita'] ?>" class="btn btn-primario btn-sm btn-atender-cita">
                                        🩺 Atender
                                    </a>
                                <?php elseif ($esFinalizada): ?>
                                    <a href="index.php?ruta=medico/atencion&id=<?= $c['id_cita'] ?>" class="btn btn-secundario btn-sm btn-atender-cita">
                                        👁 Ver Consulta
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secundario btn-sm" disabled title="Recepción debe ingresar la cita antes de atender">
                                        En Espera
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

