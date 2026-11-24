/**
 * medico.js
 * Para qué sirve: Controla el doble clic de la agenda, la adición dinámica de medicamentos y el código de receta.
 * Trabaja con: views/medico/agenda.php, views/medico/receta_form.php y views/medico/receta_imprimible.php.
 * Equivale en NetBeans a: DefaultTableModel y eventos de botones en VistaMedico.java.
 */

// Agrega una fila de medicamento a la receta
function agregarFilaMedicamento() {
    var tbody = document.getElementById('tbody-medicamentos');
    if (!tbody) return;

    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="medicamento[]" class="campo-control" placeholder="Ej: Amoxicilina" required></td>
        <td><input type="text" name="dosis[]" class="campo-control" placeholder="Ej: 500 mg" required></td>
        <td><input type="text" name="frecuencia[]" class="campo-control" placeholder="Ej: Cada 8 horas" required></td>
        <td><input type="text" name="duracion[]" class="campo-control" placeholder="Ej: 7 días" required></td>
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
            <button type="button" class="btn btn-peligro btn-sm" onclick="eliminarFilaMedicamento(this)">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
}

// Quita una fila de medicamento garantizando que quede al menos una
function eliminarFilaMedicamento(btn) {
    var tbody = document.getElementById('tbody-medicamentos');
    if (tbody && tbody.children.length > 1) {
        var tr = btn.closest('tr');
        if (tr) tr.remove();
    } else {
        alert('La receta debe contener al menos un medicamento.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Doble clic en la fila de la agenda médica para abrir la atención
    var filas = document.querySelectorAll('.fila-agenda-medico');
    filas.forEach(function(fila) {
        fila.addEventListener('dblclick', function() {
            var btn = fila.querySelector('.btn-atender-cita');
            if (btn && !btn.disabled) {
                btn.click();
            }
        });
    });

    // Renderizar código de barras en la receta médica (CODE128C para lectores láser)
    var svg = document.getElementById('barcode-receta');
    if (svg && svg.dataset.codigo && window.JsBarcode) {
        var codigoReceta = svg.dataset.codigo.trim();
        var soloDigitos = codigoReceta.replace(/\D/g, '');
        var usarModoC = soloDigitos.length > 0 && (soloDigitos.length % 2 === 0) && /^\d+$/.test(soloDigitos);
        var valorCodificar = usarModoC ? soloDigitos : codigoReceta;
        var formato = usarModoC ? "CODE128C" : "CODE128";

        try {
            JsBarcode("#barcode-receta", valorCodificar, {
                format: formato,
                lineColor: "#000000",
                background: "#FFFFFF",
                width: 2,
                height: 48,
                margin: 20,
                displayValue: false
            });
        } catch (e) {
            console.warn('Fallo al renderizar código de receta en modo C, usando CODE128:', e);
            try {
                JsBarcode("#barcode-receta", codigoReceta, {
                    format: "CODE128",
                    lineColor: "#000000",
                    background: "#FFFFFF",
                    width: 1.5,
                    height: 40,
                    margin: 10,
                    displayValue: false
                });
            } catch (err) {
                console.error('Error al generar código de receta:', err);
            }
        }
    }
});

