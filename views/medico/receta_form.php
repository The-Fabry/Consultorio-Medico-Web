<?php
/**
 * receta_form.php
 * Para qué sirve: Formulario dinámico para prescribir medicamentos y emitir la receta médica.
 * Trabaja con: controllers/MedicoController.php y assets/js/medico.js.
 * Equivale en NetBeans a: No existía en versión de escritorio (NUEVA función web).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="tarjeta" style="max-width: 850px; margin: 0 auto;">
    <div class="tarjeta-cabecera" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>💊 Prescripción de Receta Médica</h2>
        <a href="index.php?ruta=medico/atencion&id=<?= $cita['id_cita'] ?>" class="btn btn-secundario btn-sm">◀ Volver a Consulta</a>
    </div>

    <!-- Ficha rápida de paciente y médico -->
    <div class="fila-campos" style="background:#F8FAFC; padding:12px; border-radius:8px; margin-bottom:18px; font-size:0.9rem;">
        <div>
            <span style="color:#64748B; font-size:0.8rem;">PACIENTE:</span>
            <div><strong><?= htmlspecialchars($cita['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div style="color:#64748B;">C.I. <?= htmlspecialchars($cita['cedula_cliente'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
            <span style="color:#64748B; font-size:0.8rem;">MÉDICO:</span>
            <div><strong><?= htmlspecialchars($cita['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div style="color:#64748B;"><?= htmlspecialchars($cita['especialidad'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
            <span style="color:#64748B; font-size:0.8rem;">CITA:</span>
            <div><strong>#<?= str_pad((string)$cita['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong></div>
            <div style="color:#64748B;"><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></div>
        </div>
    </div>

    <form action="index.php?ruta=medico/receta_guardar" method="POST">
        <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <label class="campo-etiqueta" style="margin-bottom:0;">Lista de Medicamentos *</label>
            <button type="button" class="btn btn-secundario btn-sm" onclick="agregarFilaMedicamento()">+ Agregar Medicamento</button>
        </div>

        <!-- Tabla dinámica de medicamentos -->
        <div class="tabla-responsive" style="margin-bottom: 16px;">
            <table class="tabla-clinica">
                <thead>
                    <tr>
                        <th style="width:28%;">Medicamento</th>
                        <th style="width:18%;">Dosis</th>
                        <th style="width:20%;">Frecuencia</th>
                        <th style="width:16%;">Duración</th>
                        <th style="width:14%;">Vía</th>
                        <th style="width:4%;"></th>
                    </tr>
                </thead>
                <tbody id="tbody-medicamentos">
                    <tr>
                        <td><input type="text" name="medicamento[]" class="campo-control" placeholder="Ej: Paracetamol" required></td>
                        <td><input type="text" name="dosis[]" class="campo-control" placeholder="Ej: 500 mg" required></td>
                        <td><input type="text" name="frecuencia[]" class="campo-control" placeholder="Ej: Cada 8 horas" required></td>
                        <td><input type="text" name="duracion[]" class="campo-control" placeholder="Ej: 5 días" required></td>
                        <td>
                            <select name="via_administracion[]" class="campo-control">
                                <option value="Oral">Oral</option>
                                <option value="Intravenosa">Intravenosa</option>
                                <option value="Intramuscular">Intramuscular</option>
                                <option value="Tópica">Tópica</option>
                                <option value="Oftálmica">Oftálmica</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <button type="button" class="btn btn-peligro btn-sm" onclick="eliminarFilaMedicamento(this)" title="Quitar">✕</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="campo-grupo">
            <label for="indicaciones" class="campo-etiqueta">Indicaciones Generales y Dieta</label>
            <textarea name="indicaciones" id="indicaciones" rows="3" class="campo-control" placeholder="Ej: Tomar después de las comidas con abundante agua..."></textarea>
        </div>

        <div style="text-align:right; margin-top:20px;">
            <button type="submit" class="btn btn-primario">✔ Guardar y Emitir Receta</button>
        </div>
    </form>
</div>

<script src="assets/js/medico.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>

