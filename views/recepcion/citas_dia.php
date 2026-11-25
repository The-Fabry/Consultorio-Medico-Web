<?php
/**
 * citas_dia.php
 * Para qué sirve: Pantalla para visualizar la agenda diaria con pestañas de la semana y filtros AJAX.
 * Trabaja con: controllers/RecepcionController.php y assets/js/recepcion.js.
 * Equivale en NetBeans a: No existía como vista semanal AJAX (NUEVA función web).
 */
require_once __DIR__ . '/../header.php';
$hoy = date('Y-m-d');
?>

<div class="cabecera-seccion-flex">
    <div>
        <h2 style="color:var(--color-primario);">Agenda de Citas del Día</h2>
        <p style="color:#64748B; font-size:0.9rem;">Consulte las citas programadas y confirme la llegada de pacientes</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="index.php?ruta=recepcion/control" class="btn btn-secundario">🔍 Control por Escáner</a>
        <button type="button" class="btn btn-primario" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<!-- Filtros de fecha y navegación rápida -->
<div class="tarjeta no-print" style="padding: 14px; margin-bottom: 16px;">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;">
        <div style="display:flex; align-items:center; gap:6px;">
            <button type="button" class="btn btn-secundario btn-sm" onclick="moverDia(-1)">◀ Anterior</button>
            <button type="button" class="btn btn-secundario btn-sm" onclick="cambiarDiaAgenda('<?= $hoy ?>')">Hoy</button>
            <button type="button" class="btn btn-secundario btn-sm" onclick="moverDia(1)">Siguiente ▶</button>
            <input type="date" id="filtro-fecha" class="campo-control" value="<?= $hoy ?>" style="width:auto; font-weight:600;">
        </div>

        <div style="display:flex; gap:10px;">
            <select id="filtro-especialista" class="campo-control" style="width:auto;">
                <option value="">Todos los médicos</option>
                <?php foreach ($especialistas as $esp): ?>
                    <option value="<?= $esp['id_especialista'] ?>">
                        <?= htmlspecialchars($esp['nombre_completo'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filtro-estado" class="campo-control" style="width:auto;">
                <option value="">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="En Proceso">En Proceso</option>
                <option value="Finalizada">Finalizada</option>
                <option value="Cancelada">Cancelada</option>
            </select>
        </div>
    </div>
</div>

<!-- Pestañas de la semana (Lunes a Domingo) -->
<div class="semana-pestañas no-print" id="contenedor-semana-tabs">
    <!-- Se llena dinámicamente mediante AJAX en recepcion.js -->
</div>

<!-- Tarjetas de resumen de conteos -->
<div class="resumen-grilla">
    <div class="resumen-caja">
        <div class="resumen-num" id="cnt-total" style="color:var(--color-primario);">0</div>
        <div class="resumen-lbl">Total Citas</div>
    </div>
    <div class="resumen-caja">
        <div class="resumen-num" id="cnt-pendiente" style="color:#1D4ED8;">0</div>
        <div class="resumen-lbl">Pendientes</div>
    </div>
    <div class="resumen-caja">
        <div class="resumen-num" id="cnt-en-proceso" style="color:#B45309;">0</div>
        <div class="resumen-lbl">En Proceso</div>
    </div>
    <div class="resumen-caja">
        <div class="resumen-num" id="cnt-finalizada" style="color:#047857;">0</div>
        <div class="resumen-lbl">Finalizadas</div>
    </div>
</div>

<!-- Tabla de citas -->
<div class="tarjeta" style="padding:0; overflow:hidden;">
    <div class="tabla-responsive">
        <table class="tabla-clinica">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>N.º Cita</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                    <th style="text-align:center;" class="no-print">Acción</th>
                </tr>
            </thead>
            <tbody id="tbody-agenda-dia">
                <tr>
                    <td colspan="9" style="text-align:center; padding:30px; color:#64748B;">Cargando citas del día...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="assets/js/recepcion.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

