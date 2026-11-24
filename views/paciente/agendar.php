<?php
/**
 * agendar.php
 * Para qué sirve: Formulario interactivo unificado para que el paciente agende su cita médica.
 * Trabaja con: controllers/PacienteController.php, models/Cita.php y assets/js/paciente.js.
 * Equivale en NetBeans a: VistaPaciente.java
 */
require_once __DIR__ . '/../header.php';

// Obtener las especialidades únicas disponibles a partir del listado de médicos
$especialidades = [];
foreach ($especialistas as $esp) {
    if (!in_array($esp['especialidad'], $especialidades, true)) {
        $especialidades[] = $esp['especialidad'];
    }
}
sort($especialidades);

// Obtener el primer nombre y las iniciales del paciente para el avatar
$partesNombre = explode(' ', trim($cliente['nombre']));
$primerNombre = $partesNombre[0] ?? 'Paciente';
$iniciales = '';
if (isset($partesNombre[0])) $iniciales .= strtoupper(substr($partesNombre[0], 0, 1));
if (isset($partesNombre[1])) $iniciales .= strtoupper(substr($partesNombre[1], 0, 1));
?>

<!-- Banner Superior de Bienvenida con degradado médico -->
<div class="banner-agendar-bienvenida">
    <div class="banner-icono-saludo">👋</div>
    <div>
        <h2>Hola, <?= htmlspecialchars($primerNombre, ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Agenda tu cita médica completando el formulario. Selecciona especialidad, médico, fecha y horario.</p>
    </div>
</div>

<div class="agendar-layout-grid">
    <!-- Columna Izquierda: Formulario Unificado (Todos los campos visibles a la vez) -->
    <div class="agendar-columna-formulario">

        <div class="tarjeta-formulario-agendar">
            <div class="formulario-agendar-cabecera">
                <h3 class="formulario-titulo">Agenda tu cita</h3>
                <p class="formulario-subtitulo">Completa los campos a continuación para reservar tu atención médica.</p>
            </div>

            <!-- Campo 1: Especialidad Médica -->
            <div class="campo-grupo-agendar">
                <label for="select-especialidad" class="etiqueta-agendar">
                    <span class="numero-guia">1</span> Especialidad Médica
                </label>
                <select id="select-especialidad" class="control-agendar">
                    <option value="">Selecciona una especialidad</option>
                    <?php foreach ($especialidades as $esp): ?>
                        <option value="<?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="ayuda-agendar">Área médica en la que deseas atenderte.</small>
            </div>

            <!-- Campo 2: Médico Especialista -->
            <div class="campo-grupo-agendar">
                <label for="buscador-medico" class="etiqueta-agendar">
                    <span class="numero-guia">2</span> Médico Especialista
                </label>
                <input type="text" id="buscador-medico" class="control-agendar" placeholder="Buscar médico por nombre..." autocomplete="off" disabled>
                
                <!-- Caja con scroll para la lista de médicos filtrados -->
                <div id="lista-medicos-caja" class="lista-medicos-scroll" style="display: none;"></div>
                <small id="ayuda-medico" class="ayuda-agendar">Primero selecciona una especialidad para ver los médicos disponibles.</small>
            </div>

            <!-- Fila en 2 Columnas para Fecha y Hora -->
            <div class="fila-fecha-hora-grid">
                <!-- Campo 3: Fecha -->
                <div class="campo-grupo-agendar">
                    <label for="campo-fecha" class="etiqueta-agendar">
                        <span class="numero-guia">3</span> Fecha de la Cita
                    </label>
                    <div class="input-con-boton-fecha">
                        <input type="date" id="campo-fecha" class="control-agendar" min="<?= date('Y-m-d') ?>" disabled>
                        <button type="button" id="btn-abrir-calendario" class="btn-icono-calendario" title="Abrir calendario" disabled>
                            📅
                        </button>
                    </div>
                    <div id="texto-fecha-seleccionada" class="texto-fecha-legible" style="display: none;"></div>
                    <small id="ayuda-fecha" class="ayuda-agendar">Selecciona un médico para habilitar el calendario.</small>
                </div>

                <!-- Campo 4: Hora -->
                <div class="campo-grupo-agendar">
                    <label for="select-hora" class="etiqueta-agendar">
                        <span class="numero-guia">4</span> Horario de Atención
                    </label>
                    <select id="select-hora" class="control-agendar" disabled>
                        <option value="">Selecciona una hora</option>
                    </select>
                    <div id="info-turnos-libres" class="info-cupos-texto" style="display: none;"></div>
                    <small id="ayuda-hora" class="ayuda-agendar">Selecciona médico y fecha para consultar horarios disponibles.</small>
                </div>
            </div>

        </div>

    </div>

    <!-- Columna Derecha: Datos del Paciente, Próxima Cita y Resumen en Vivo (INTACTA) -->
    <div class="agendar-columna-lateral">

        <!-- Tarjeta de Perfil Compacto del Paciente -->
        <div class="tarjeta-lateral-perfil">
            <div class="perfil-avatar-circulo"><?= $iniciales ?></div>
            <div class="perfil-info">
                <h4><?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p>C.I. <?= htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8') ?> &bull; Tel: <?= htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <!-- Tarjeta de Tu Próxima Cita (si tiene alguna) -->
        <div class="tarjeta-proxima-cita">
            <div class="proxima-cita-titulo">
                <span>📌</span> Tu próxima cita programada
            </div>
            <?php if ($proximaCita): ?>
                <div class="proxima-cita-cuerpo">
                    <div class="proxima-cita-medico"><?= htmlspecialchars($proximaCita['nombre_especialista'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="proxima-cita-esp"><?= htmlspecialchars($proximaCita['especialidad'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="proxima-cita-fecha">
                        📅 <?= date('d/m/Y H:i', strtotime($proximaCita['fecha_cita'])) ?>
                    </div>
                    <a href="index.php?ruta=paciente/citas" class="proxima-cita-link">Ver en Mis Citas &rarr;</a>
                </div>
            <?php else: ?>
                <p class="proxima-cita-vacia">Aún no tienes citas programadas pendientes.</p>
            <?php endif; ?>
        </div>

        <!-- Tarjeta de Resumen de la Cita en Vivo y Formulario POST -->
        <div class="tarjeta-resumen-flotante">
            <h3 class="resumen-titulo">📋 Resumen de tu Cita</h3>
            
            <div class="resumen-items-lista">
                <div class="resumen-item">
                    <span class="resumen-item-lbl">Especialidad:</span>
                    <strong class="resumen-item-val" id="resumen-especialidad">Sin seleccionar</strong>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item-lbl">Médico:</span>
                    <strong class="resumen-item-val" id="resumen-medico">Sin seleccionar</strong>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item-lbl">Fecha:</span>
                    <strong class="resumen-item-val" id="resumen-fecha">Sin seleccionar</strong>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item-lbl">Hora:</span>
                    <strong class="resumen-item-val" id="resumen-hora">Sin seleccionar</strong>
                </div>
            </div>

            <!-- Formulario real enviado al servidor -->
            <form id="form-agendar-cita" action="index.php?ruta=paciente/agendar" method="POST">
                <input type="hidden" name="id_especialista" id="input-id-especialista" required>
                <input type="hidden" name="fecha" id="input-fecha" required>
                <input type="hidden" name="hora" id="input-hora" required>

                <button type="submit" id="btn-confirmar-cita" class="btn btn-primario btn-confirmar-agendar" disabled>
                    Confirmar y Agendar Cita
                </button>
            </form>

            <div style="text-align: center; margin-top: 14px;">
                <a href="index.php?ruta=paciente/citas" style="font-size: 0.88rem; color: var(--color-texto-muted);">
                    Ver mis citas agendadas
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Se inyecta la lista de especialistas en JSON para filtrado veloz en JavaScript -->
<script>
    var LISTA_ESPECIALISTAS = <?= json_encode($especialistas, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/paciente.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>
