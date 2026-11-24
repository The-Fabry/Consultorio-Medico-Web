/**
 * paciente.js
 * Para qué sirve: Controla el formulario unificado de agendamiento de citas
 *                 (especialidad, médico, fecha y hora), filtrado en tiempo real,
 *                 consulta AJAX de horarios y generación de código de barras CODE_128C.
 * Trabaja con: views/paciente/agendar.php y views/paciente/ticket.php.
 * Equivale en NetBeans a: Eventos de selección en VistaPaciente.java y TicketDigita.java.
 */

// Estado global de la cita seleccionada
var seleccionCita = {
    especialidad: '',
    idEspecialista: '',
    nombreMedico: '',
    fecha: '',
    fechaTexto: '',
    hora: ''
};

// Nombres para formateo amigable de fechas en español
var NOMBRES_DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
var NOMBRES_MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

// Definición de las horas agrupadas por jornada (Mañana y Tarde)
// Mañana: 07:00 a 12:00 (cada 30 min, excluye 06:00 y 06:30)
// Tarde:  13:30 a 19:00 (cada 30 min, hasta las 7:00 p. m.)
var GRUPOS_HORAS = [
    {
        etiqueta: 'Mañana (07:00 a 12:00)',
        horas: [
            '07:00', '07:30', '08:00', '08:30',
            '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '12:00'
        ]
    },
    {
        etiqueta: 'Tarde (13:30 a 19:00)',
        horas: [
            '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30',
            '17:00', '17:30', '18:00', '18:30', '19:00'
        ]
    }
];

// Obtiene las 2 iniciales de un médico para el avatar circular
// Recibe: nombre completo. Devuelve: texto de 2 caracteres.
function obtenerIniciales(nombre) {
    var limpio = nombre.replace(/^Dr\.\s+|^Dra\.\s+/i, '').trim();
    var partes = limpio.split(' ');
    var ini = '';
    if (partes[0]) ini += partes[0].charAt(0).toUpperCase();
    if (partes[1]) ini += partes[1].charAt(0).toUpperCase();
    return ini || 'DR';
}

// Convierte una fecha YYYY-MM-DD en texto legible (ej: 'Martes 01 de diciembre')
// Recibe: fechaStr en formato 'YYYY-MM-DD'. Devuelve: fecha formateada en español.
function formatearFechaTexto(fechaStr) {
    if (!fechaStr) return '';
    var partes = fechaStr.split('-');
    if (partes.length !== 3) return fechaStr;
    var d = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
    var diaSem = NOMBRES_DIAS[d.getDay()];
    var mes = NOMBRES_MESES[d.getMonth()];
    return diaSem + ' ' + partes[2] + ' de ' + mes;
}

// Inicializa todos los eventos y controles del formulario de agendar cita
// Recibe: nada. Devuelve: nada.
function inicializarFormularioAgendar() {
    var selectEsp = document.getElementById('select-especialidad');
    var buscadorMed = document.getElementById('buscador-medico');
    var campoFecha = document.getElementById('campo-fecha');
    var btnCal = document.getElementById('btn-abrir-calendario');
    var selectHora = document.getElementById('select-hora');

    if (!selectEsp) return;

    // 1. Cambio de Especialidad
    selectEsp.addEventListener('change', function() {
        var esp = this.value;
        seleccionCita.especialidad = esp;
        document.getElementById('resumen-especialidad').textContent = esp || 'Sin seleccionar';

        // Al cambiar especialidad, se limpian médico y hora (la fecha se conserva)
        limpiarMedico();
        limpiarHora();

        if (esp !== '') {
            buscadorMed.disabled = false;
            buscadorMed.value = '';
            document.getElementById('ayuda-medico').style.display = 'none';
            document.getElementById('lista-medicos-caja').style.display = 'block';
            renderizarMedicos('');
        } else {
            buscadorMed.disabled = true;
            buscadorMed.value = '';
            document.getElementById('ayuda-medico').style.display = 'block';
            document.getElementById('lista-medicos-caja').style.display = 'none';

            // Deshabilitar fecha y hora
            campoFecha.disabled = true;
            btnCal.disabled = true;
            document.getElementById('ayuda-fecha').style.display = 'block';
        }

        actualizarBotonConfirmar();
    });

    // 2. Buscador en vivo de médicos
    buscadorMed.addEventListener('input', function() {
        renderizarMedicos(this.value);
    });

    // 3. Botón de calendario (showPicker con fallback)
    btnCal.addEventListener('click', function() {
        if (campoFecha.disabled) return;
        if (typeof campoFecha.showPicker === 'function') {
            try {
                campoFecha.showPicker();
            } catch (e) {
                campoFecha.focus();
            }
        } else {
            campoFecha.focus();
        }
    });

    // 4. Cambio en el campo de fecha
    campoFecha.addEventListener('change', function() {
        var fechaVal = this.value;
        if (!fechaVal) return;

        seleccionCita.fecha = fechaVal;
        document.getElementById('input-fecha').value = fechaVal;

        // Mostrar texto de fecha legible debajo del campo y en el resumen
        var textoLegible = formatearFechaTexto(fechaVal);
        seleccionCita.fechaTexto = textoLegible;

        var divFecha = document.getElementById('texto-fecha-seleccionada');
        divFecha.textContent = '📅 ' + textoLegible;
        divFecha.style.display = 'block';

        document.getElementById('resumen-fecha').textContent = textoLegible;

        // Limpiar hora previa y recargar horarios si ya hay médico
        limpiarHora();
        if (seleccionCita.idEspecialista) {
            cargarHorarios();
        }

        actualizarBotonConfirmar();
    });

    // 5. Cambio en el selector de hora
    selectHora.addEventListener('change', function() {
        var horaVal = this.value;
        seleccionCita.hora = horaVal;
        document.getElementById('input-hora').value = horaVal;
        document.getElementById('resumen-hora').textContent = horaVal || 'Sin seleccionar';

        actualizarBotonConfirmar();
    });
}

// Filtra y pinta la lista de médicos en la caja con scroll
// Recibe: textoFiltro para buscar en el nombre. Devuelve: nada.
function renderizarMedicos(textoFiltro) {
    var caja = document.getElementById('lista-medicos-caja');
    if (!caja || typeof LISTA_ESPECIALISTAS === 'undefined') return;

    var txt = (textoFiltro || '').trim().toLowerCase();
    var espActual = seleccionCita.especialidad.toLowerCase();

    var filtrados = LISTA_ESPECIALISTAS.filter(function(m) {
        var coincideEsp = (m.especialidad.toLowerCase() === espActual);
        var coincideTxt = true;
        if (txt !== '') {
            coincideTxt = m.nombre_completo.toLowerCase().indexOf(txt) !== -1;
        }
        return coincideEsp && coincideTxt;
    });

    if (filtrados.length === 0) {
        caja.innerHTML = '<div class="aviso-sin-medicos">No se encontraron médicos con ese nombre.</div>';
        return;
    }

    var html = '';
    filtrados.forEach(function(m) {
        var ini = obtenerIniciales(m.nombre_completo);
        var sel = (String(seleccionCita.idEspecialista) === String(m.id_especialista)) ? 'seleccionado' : '';

        html += '<div class="medico-fila-item ' + sel + '" data-id="' + m.id_especialista + '" data-nombre="' + m.nombre_completo + '" onclick="seleccionarFilaMedico(this)">';
        html += '  <div class="medico-fila-avatar">' + ini + '</div>';
        html += '  <div class="medico-fila-info">';
        html += '    <p class="medico-fila-nombre">' + m.nombre_completo + '</p>';
        html += '    <p class="medico-fila-esp">' + m.especialidad + '</p>';
        html += '  </div>';
        html += '</div>';
    });

    caja.innerHTML = html;
}

// Maneja el clic sobre una fila de médico
// Recibe: elemento DOM de la fila tocada. Devuelve: nada.
function seleccionarFilaMedico(elemento) {
    var id = elemento.dataset.id;
    var nombre = elemento.dataset.nombre;
    if (!id) return;

    // Resaltar visualmente la fila seleccionada
    var filas = document.querySelectorAll('.medico-fila-item');
    filas.forEach(function(f) { f.classList.remove('seleccionado'); });
    elemento.classList.add('seleccionado');

    seleccionCita.idEspecialista = id;
    seleccionCita.nombreMedico = nombre;
    document.getElementById('input-id-especialista').value = id;
    document.getElementById('resumen-medico').textContent = nombre;

    // Habilitar campo de fecha y botón de calendario
    var campoFecha = document.getElementById('campo-fecha');
    var btnCal = document.getElementById('btn-abrir-calendario');
    campoFecha.disabled = false;
    btnCal.disabled = false;
    document.getElementById('ayuda-fecha').style.display = 'none';

    // Limpiar hora previa
    limpiarHora();

    // Si ya había una fecha seleccionada, recargar las horas para este nuevo médico
    if (seleccionCita.fecha) {
        cargarHorarios();
    }

    actualizarBotonConfirmar();
}

// Consulta vía AJAX los horarios ocupados y genera las opciones agrupadas con optgroup
// Recibe: nada. Devuelve: nada.
function cargarHorarios() {
    var selectHora = document.getElementById('select-hora');
    var infoCupos = document.getElementById('info-turnos-libres');
    var ayudaHora = document.getElementById('ayuda-hora');

    if (!seleccionCita.idEspecialista || !seleccionCita.fecha) return;

    var url = 'index.php?ruta=api/horarios-ocupados&id_especialista=' +
              encodeURIComponent(seleccionCita.idEspecialista) +
              '&fecha=' + encodeURIComponent(seleccionCita.fecha);

    selectHora.disabled = true;
    selectHora.innerHTML = '<option value="">Consultando horarios...</option>';

    fetch(url)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var ocupadas = data.ocupadas || [];
            construirOpcionesHoras(ocupadas);
        })
        .catch(function(err) {
            console.error('Error al consultar horarios:', err);
            selectHora.innerHTML = '<option value="">Error al cargar horarios</option>';
        });
}

// Construye el HTML con optgroup y calcula los cupos libres
// Recibe: arreglo de horas ocupadas ['08:00', ...]. Devuelve: nada.
function construirOpcionesHoras(ocupadas) {
    var selectHora = document.getElementById('select-hora');
    var infoCupos = document.getElementById('info-turnos-libres');
    var ayudaHora = document.getElementById('ayuda-hora');

    var hoy = new Date();
    var yyyy = hoy.getFullYear();
    var mm = String(hoy.getMonth() + 1).padStart(2, '0');
    var dd = String(hoy.getDate()).padStart(2, '0');
    var hoyStr = yyyy + '-' + mm + '-' + dd;
    var esFechaHoy = (seleccionCita.fecha === hoyStr);
    var minutosActuales = hoy.getHours() * 60 + hoy.getMinutes();

    var totalLibres = 0;
    var html = '<option value="">Selecciona una hora</option>';

    GRUPOS_HORAS.forEach(function(grupo) {
        html += '<optgroup label="' + grupo.etiqueta + '">';

        grupo.horas.forEach(function(h) {
            var partesH = h.split(':');
            var minTurno = parseInt(partesH[0], 10) * 60 + parseInt(partesH[1], 10);

            var estaOcupada = (ocupadas.indexOf(h) !== -1);
            var estaPasada = (esFechaHoy && minTurno <= minutosActuales);

            if (estaOcupada) {
                html += '<option value="' + h + '" disabled>' + h + ' (Ocupado)</option>';
            } else if (estaPasada) {
                html += '<option value="' + h + '" disabled>' + h + ' (No disponible)</option>';
            } else {
                totalLibres++;
                var sel = (seleccionCita.hora === h) ? 'selected' : '';
                html += '<option value="' + h + '" ' + sel + '>' + h + '</option>';
            }
        });

        html += '</optgroup>';
    });

    selectHora.innerHTML = html;
    selectHora.disabled = false;
    ayudaHora.style.display = 'none';

    // Mostrar mensaje con el total de turnos libres
    infoCupos.style.display = 'block';
    if (totalLibres > 0) {
        infoCupos.innerHTML = '<span style="color: var(--color-exito); font-weight: 700;">✓ ' + totalLibres + ' horarios libres</span>';
    } else {
        infoCupos.innerHTML = '<span style="color: var(--color-peligro); font-weight: 700;">⚠ Este médico no tiene horarios libres ese día, prueba con otra fecha.</span>';
    }
}

// Limpia la selección del médico y reinicia su fila activa
// Recibe: nada. Devuelve: nada.
function limpiarMedico() {
    seleccionCita.idEspecialista = '';
    seleccionCita.nombreMedico = '';
    document.getElementById('input-id-especialista').value = '';
    document.getElementById('resumen-medico').textContent = 'Sin seleccionar';

    var filas = document.querySelectorAll('.medico-fila-item');
    filas.forEach(function(f) { f.classList.remove('seleccionado'); });
}

// Limpia el selector de hora, vacía sus opciones y oculta los avisos
// Recibe: nada. Devuelve: nada.
function limpiarHora() {
    seleccionCita.hora = '';
    document.getElementById('input-hora').value = '';
    document.getElementById('resumen-hora').textContent = 'Sin seleccionar';

    var selectHora = document.getElementById('select-hora');
    selectHora.innerHTML = '<option value="">Selecciona una hora</option>';
    selectHora.disabled = true;

    var infoCupos = document.getElementById('info-turnos-libres');
    infoCupos.style.display = 'none';

    var ayudaHora = document.getElementById('ayuda-hora');
    ayudaHora.style.display = 'block';
}

// Activa el botón de Confirmar solo cuando los 4 datos están presentes
// Recibe: nada. Devuelve: nada.
function actualizarBotonConfirmar() {
    var btn = document.getElementById('btn-confirmar-cita');
    if (!btn) return;

    var completo = (
        seleccionCita.especialidad !== '' &&
        seleccionCita.idEspecialista !== '' &&
        seleccionCita.fecha !== '' &&
        seleccionCita.hora !== ''
    );

    btn.disabled = !completo;
}

// Dibuja el código de barras en el ticket digital con formato CODE_128 modo C
// Recibe: nada. Devuelve: nada.
function inicializarCodigoBarrasTicket() {
    var svg = document.getElementById('barcode');
    if (!svg || !svg.dataset.codigo || !window.JsBarcode) return;

    var codigoCompleto = svg.dataset.codigo.trim();
    var soloDigitos = codigoCompleto.replace(/\D/g, '');

    var esParYDigitos = soloDigitos.length > 0 && (soloDigitos.length % 2 === 0) && /^\d+$/.test(soloDigitos);
    var valorCodificar = esParYDigitos ? soloDigitos : codigoCompleto;
    var formato = esParYDigitos ? 'CODE128C' : 'CODE128';

    try {
        JsBarcode('#barcode', valorCodificar, {
            format: formato,
            width: 3,
            height: 70,
            margin: 36,
            background: '#FFFFFF',
            lineColor: '#000000',
            displayValue: false
        });
    } catch (e) {
        console.warn('Fallo al generar en CODE128C, usando CODE128:', e);
        try {
            JsBarcode('#barcode', codigoCompleto, {
                format: 'CODE128',
                width: 2,
                height: 70,
                margin: 20,
                background: '#FFFFFF',
                lineColor: '#000000',
                displayValue: false
            });
        } catch (err) {
            console.error('Error crítico al generar código de barras:', err);
        }
    }
}

// Inicialización general del DOM
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar formulario de agendar si estamos en agendar.php
    if (document.getElementById('select-especialidad')) {
        inicializarFormularioAgendar();
    }

    // Inicializar código de barras si estamos en ticket.php
    inicializarCodigoBarrasTicket();
});
