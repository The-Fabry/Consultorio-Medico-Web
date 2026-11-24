<?php
/**
 * ticket.php
 * Para qué sirve: Muestra el ticket digital con código de barras CODE_128 escaneable.
 * Trabaja con: controllers/PacienteController.php, JsBarcode y print.
 * Equivale en NetBeans a: TicketDigita.java
 */
require_once __DIR__ . '/../header.php';
$fechaFormato = date('d/m/Y H:i', strtotime($cita['fecha_cita']));
?>

<div class="tarjeta-ticket">
    <div class="ticket-cabecera">
        <h2>SmartMed Consultorio</h2>
        <p>Comprobante Digital de Cita Médica</p>
    </div>

    <div class="ticket-cuerpo">
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Número de Cita:</span>
            <strong>#<?= str_pad((string)$cita['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Paciente:</span>
            <strong><?= htmlspecialchars($cita['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Cédula:</span>
            <span><?= htmlspecialchars($cita['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Especialista:</span>
            <strong><?= htmlspecialchars($cita['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Especialidad:</span>
            <span><?= htmlspecialchars($cita['especialidad'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Fecha y Hora:</span>
            <strong><?= $fechaFormato ?></strong>
        </div>
        <div class="ticket-fila">
            <span class="ticket-etiqueta">Estado:</span>
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $cita['estado'])) ?>">
                <?= htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>

    <!-- Código de barras CODE_128 modo C escaneable por lector láser -->
    <div class="ticket-codigo-box" style="text-align: center; padding: 15px; background: #ffffff; border-radius: 8px;">
        <svg id="barcode" data-codigo="<?= htmlspecialchars($cita['codigo_barras'], ENT_QUOTES, 'UTF-8') ?>"></svg>
        <!-- Código completo legible debajo del código de barras en fuente monoespaciada -->
        <div style="font-family: 'Courier New', Courier, monospace; font-size: 1.15rem; font-weight: 700; letter-spacing: 2px; margin-top: 6px; color: #0F172A;">
            <?= htmlspecialchars($cita['codigo_barras'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <p style="font-size: 0.8rem; color:#64748B; margin-top:6px;">
            Presente este código en recepción al llegar al consultorio.
        </p>
    </div>
</div>

<div class="no-print" style="text-align:center; margin-top: 18px;">
    <a href="index.php?ruta=paciente/citas" class="btn btn-secundario">Volver a Mis Citas</a>
    <button type="button" class="btn btn-primario" onclick="window.print()">🖨 Imprimir Ticket</button>
</div>

<!-- Librería local JsBarcode y script del paciente -->
<script src="assets/js/JsBarcode.all.min.js"></script>
<script src="assets/js/paciente.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

