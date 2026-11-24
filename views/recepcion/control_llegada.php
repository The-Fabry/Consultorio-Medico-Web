<?php
/**
 * control_llegada.php
 * Para qué sirve: Pantalla principal de recepción para validar tickets por lector láser o manualmente.
 * Trabaja con: controllers/RecepcionController.php y assets/js/recepcion.js.
 * Equivale en NetBeans a: VistaRecepcion.java
 */
require_once __DIR__ . '/../header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
    <div>
        <h2 style="color:var(--color-primario);">Control de Llegada de Pacientes</h2>
        <p style="color:#64748B; font-size:0.9rem;">Escanee el código de barras o ingréselo manualmente</p>
    </div>
    <a href="index.php?ruta=recepcion/citas_dia" class="btn btn-secundario">
        📆 Ver Citas del Día
    </a>
</div>

<!-- Caja de escaneo láser -->
<div class="caja-escaner">
    <div style="font-size:2rem; margin-bottom:6px;">🏷</div>
    <h3 style="color:var(--color-primario);">Lector de Código de Barras</h3>
    <p style="color:#64748B; font-size:0.85rem;">Pase la pistola láser sobre el ticket o escriba el código y presione Enter</p>

    <form id="form-escaner" action="index.php?ruta=recepcion/validar" method="POST" autocomplete="off">
        <div style="max-width: 480px; margin: 15px auto 0; display:flex; gap:10px;">
            <input type="text" id="codigo_barras" name="codigo_barras" class="campo-control input-escaner" placeholder="Ej: CITA-1712345678-1930" value="<?= htmlspecialchars($codigo ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            <button type="submit" class="btn btn-primario">Validar</button>
        </div>
    </form>
</div>

<!-- Ficha de la cita encontrada -->
<?php if (!empty($citaEncontrada)): ?>
    <?php 
        $estado = $citaEncontrada['estado'];
        $esPendiente = ($estado === 'Pendiente');
    ?>

    <div class="tarjeta" style="margin-top: 20px;">
        <div class="tarjeta-cabecera" style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Ficha de la Cita #<?= str_pad((string)$citaEncontrada['id_cita'], 5, '0', STR_PAD_LEFT) ?></h3>
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $estado)) ?>" style="font-size: 0.95rem; padding: 6px 14px;">
                <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <div class="fila-campos" style="margin-bottom: 20px;">
            <div>
                <span style="font-size:0.8rem; color:#64748B;">PACIENTE:</span>
                <div style="font-weight:600;"><?= htmlspecialchars($citaEncontrada['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></div>
                <div style="font-size:0.85rem; color:#64748B;">C.I. <?= htmlspecialchars($citaEncontrada['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?> &bull; Tel: <?= htmlspecialchars($citaEncontrada['telefono'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div>
                <span style="font-size:0.8rem; color:#64748B;">ESPECIALISTA:</span>
                <div style="font-weight:600;"><?= htmlspecialchars($citaEncontrada['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></div>
                <div style="font-size:0.85rem; color:#64748B;"><?= htmlspecialchars($citaEncontrada['especialidad'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div>
                <span style="font-size:0.8rem; color:#64748B;">FECHA Y HORA:</span>
                <div style="font-weight:600;"><?= date('d/m/Y H:i', strtotime($citaEncontrada['fecha_cita'])) ?></div>
                <div style="font-size:0.85rem; color:#64748B;">Código: <?= htmlspecialchars($citaEncontrada['codigo_barras'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

        <!-- Mensajes según el estado de la cita -->
        <?php if ($estado === 'Pendiente'): ?>
            <div class="alerta alerta-info" style="margin-bottom: 16px;">
                <span>ℹ</span> El paciente está en espera. Presione <strong>Ingresar a la cita</strong> para habilitar su atención con el médico.
            </div>
            <form action="index.php?ruta=recepcion/ingresar" method="POST" style="text-align:right;">
                <input type="hidden" name="id_cita" value="<?= $citaEncontrada['id_cita'] ?>">
                <input type="hidden" name="retorno" value="control">
                <button type="submit" class="btn btn-exito" style="font-size: 1rem; padding: 10px 20px;">
                    ✔ Ingresar a la Cita
                </button>
            </form>
        <?php elseif ($estado === 'En Proceso'): ?>
            <div class="alerta alerta-aviso" style="margin-bottom: 16px;">
                <span>⚠</span> El paciente ya fue ingresado y se encuentra <strong>En Proceso</strong> de atención.
            </div>
            <button class="btn btn-secundario" disabled>Botón Bloqueado (Ya está en atención)</button>
        <?php elseif ($estado === 'Finalizada'): ?>
            <div class="alerta alerta-exito" style="margin-bottom: 16px;">
                <span>✓</span> Esta cita médica ya fue atendida y <strong>Finalizada</strong> por el especialista.
            </div>
            <button class="btn btn-secundario" disabled>Botón Bloqueado (Cita Finalizada)</button>
        <?php elseif ($estado === 'Cancelada'): ?>
            <div class="alerta alerta-error" style="margin-bottom: 16px;">
                <span>✕</span> Esta cita fue <strong>Cancelada</strong> por el paciente.
            </div>
            <button class="btn btn-secundario" disabled>Botón Bloqueado (Cita Cancelada)</button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script src="assets/js/recepcion.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

