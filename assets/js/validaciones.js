/**
 * validaciones.js
 * Para qué sirve: Filtra la entrada del teclado en tiempo real para cédula, teléfono y nombres.
 * Trabaja con: views/registro.php, views/paciente/agendar.php y demás formularios.
 * Equivale en NetBeans a: KeyListeners en los campos jTextField de Swing.
 */

// Permite únicamente el ingreso de números en el campo.
// Recibe: evento del teclado. Devuelve: boolean.
function soloNumeros(e) {
    var codigo = e.which ? e.which : e.keyCode;
    // Permite teclas especiales como borrar (8) y tabulador (9)
    if (codigo === 8 || codigo === 9 || (codigo >= 48 && codigo <= 57)) {
        return true;
    }
    e.preventDefault();
    return false;
}

// Permite únicamente letras, tildes, eñes y espacios.
// Recibe: evento del teclado. Devuelve: boolean.
function soloLetras(e) {
    var tecla = e.key;
    var patron = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/;
    if (e.keyCode === 8 || e.keyCode === 9 || patron.test(tecla)) {
        return true;
    }
    e.preventDefault();
    return false;
}

// Asocia los eventos automáticamente a los campos con clases de utilidad
document.addEventListener('DOMContentLoaded', function() {
    var numeros = document.querySelectorAll('.input-solo-numeros');
    numeros.forEach(function(input) {
        input.addEventListener('keypress', soloNumeros);
    });

    var letras = document.querySelectorAll('.input-solo-letras');
    letras.forEach(function(input) {
        input.addEventListener('keypress', soloLetras);
    });
});

