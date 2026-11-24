/**
 * recepcion.js
 * Para qué sirve: Mantiene el cursor en el escáner láser y actualiza la agenda semanal por AJAX.
 * Trabaja con: views/recepcion/control_llegada.php y views/recepcion/citas_dia.php.
 * Equivale en NetBeans a: KeyListener del escáner y JTable de VistaRecepcion.java.
 */

var procesando = false;

// Mantiene el cursor enfocado permanentemente en el campo de escaneo
function autoEnfocarEscaner() {
    var input = document.getElementById('codigo_barras');
    if (!input) return;

    input.focus();
    document.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && e.target.tagName !== 'SELECT') {
            input.focus();
        }
    });
}

// Carga las citas y pestañas semanales de una fecha vía fetch
function cargarAgendaDia(fecha, idEspecialista, estado) {
    var contenedorTabs = document.getElementById('contenedor-semana-tabs');
    var tbody = document.getElementById('tbody-agenda-dia');
    var inputFecha = document.getElementById('filtro-fecha');

    if (!tbody) return;
    if (inputFecha && fecha) inputFecha.value = fecha;

    var url = 'index.php?ruta=api/citas-dia&fecha=' + encodeURIComponent(fecha);
    if (idEspecialista) url += '&id_especialista=' + encodeURIComponent(idEspecialista);
    if (estado) url += '&estado=' + encodeURIComponent(estado);

    fetch(url)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            // 1. Renderizar pestañas de la semana
            if (contenedorTabs && data.dias_semana) {
                var htmlTabs = '';
                data.dias_semana.forEach(function(d) {
                    var activo = d.es_seleccionado ? 'activo' : '';
                    htmlTabs += '<div class="tab-dia ' + activo + '" onclick="cambiarDiaAgenda(\'' + d.fecha + '\')">';
                    htmlTabs += '  <div class="tab-dia-nombre">' + d.etiqueta + '</div>';
                    htmlTabs += '  <div class="tab-dia-num">' + d.dia_numero + '</div>';
                    htmlTabs += '  <div class="tab-dia-conteo">' + d.total_citas + ' citas</div>';
                    htmlTabs += '</div>';
                });
                contenedorTabs.innerHTML = htmlTabs;
            }

            // 2. Actualizar tarjetas de conteo
            if (data.conteos) {
                document.getElementById('cnt-total').textContent = data.conteos.total;
                document.getElementById('cnt-pendiente').textContent = data.conteos.pendiente;
                document.getElementById('cnt-en-proceso').textContent = data.conteos.en_proceso;
                document.getElementById('cnt-finalizada').textContent = data.conteos.finalizada;
            }

            // 3. Renderizar las filas de la tabla
            if (!data.citas || data.citas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:25px; color:#64748B;">No hay citas para esta fecha y filtros.</td></tr>';
                return;
            }

            var html = '';
            data.citas.forEach(function(c) {
                var hora = c.fecha_cita.split(' ')[1].substring(0, 5);
                var badge = 'badge-' + c.estado.toLowerCase().replace(' ', '-');

                html += '<tr>';
                html += '  <td><strong>' + hora + '</strong></td>';
                html += '  <td>#' + String(c.id_cita).padStart(5, '0') + '</td>';
                html += '  <td>' + c.nombre_cliente + '</td>';
                html += '  <td>' + c.cedula_cliente + '</td>';
                html += '  <td>' + c.telefono + '</td>';
                html += '  <td>' + c.nombre_especialista + '</td>';
                html += '  <td>' + c.especialidad + '</td>';
                html += '  <td><span class="badge ' + badge + '">' + c.estado + '</span></td>';
                html += '  <td style="text-align:center;">';
                if (c.estado === 'Pendiente') {
                    html += '    <form action="index.php?ruta=recepcion/ingresar" method="POST" style="display:inline;">';
                    html += '      <input type="hidden" name="id_cita" value="' + c.id_cita + '">';
                    html += '      <input type="hidden" name="retorno" value="citas_dia">';
                    html += '      <button type="submit" class="btn btn-primario btn-sm">Ingresar</button>';
                    html += '    </form>';
                } else {
                    html += '    <span style="color:#94A3B8; font-size:0.85rem;">-</span>';
                }
                html += '  </td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        })
        .catch(function(err) {
            console.error('Error al actualizar agenda:', err);
        });
}

function cambiarDiaAgenda(fecha) {
    var esp = document.getElementById('filtro-especialista') ? document.getElementById('filtro-especialista').value : '';
    var est = document.getElementById('filtro-estado') ? document.getElementById('filtro-estado').value : '';
    cargarAgendaDia(fecha, esp, est);
}

function moverDia(dias) {
    var inputFecha = document.getElementById('filtro-fecha');
    if (!inputFecha) return;
    var f = new Date(inputFecha.value + 'T00:00:00');
    f.setDate(f.getDate() + dias);
    cambiarDiaAgenda(f.toISOString().split('T')[0]);
}

document.addEventListener('DOMContentLoaded', function() {
    autoEnfocarEscaner();

    // Evitar doble envío desde la pistola de código de barras
    var form = document.getElementById('form-escaner');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (procesando) {
                e.preventDefault();
                return false;
            }
            procesando = true;
        });
    }

    // Inicializar agenda diaria si estamos en esa pantalla
    var inputF = document.getElementById('filtro-fecha');
    if (inputF) {
        cambiarDiaAgenda(inputF.value);

        inputF.addEventListener('change', function() { cambiarDiaAgenda(this.value); });
        var sEsp = document.getElementById('filtro-especialista');
        var sEst = document.getElementById('filtro-estado');
        if (sEsp) sEsp.addEventListener('change', function() { cambiarDiaAgenda(inputF.value); });
        if (sEst) sEst.addEventListener('change', function() { cambiarDiaAgenda(inputF.value); });
    }
});

