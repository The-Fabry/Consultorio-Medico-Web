<?php
/**
 * registro.php
 * Para qué sirve: Pantalla para que nuevos pacientes creen su propia cuenta en el sistema.
 * Trabaja con: controllers/AuthController.php, views/header.php y views/footer.php.
 * Equivale en NetBeans a: No existía en versión de escritorio (NUEVA función web).
 */
require_once __DIR__ . '/header.php';
?>

<div class="tarjeta-auth" style="max-width: 520px; width: 100%;">
    <div class="auth-cabecera">
        <h2>Crear Cuenta de Paciente</h2>
        <p>Complete sus datos personales para agendar sus citas médicas</p>
    </div>

    <form action="index.php?ruta=registro" method="POST" autocomplete="off">
        <div class="campo-grupo">
            <label for="nombre" class="campo-etiqueta">Nombre Completo *</label>
            <input type="text" id="nombre" name="nombre" class="campo-control input-solo-letras" placeholder="Ej: Juan Pérez" required>
        </div>

        <div class="fila-campos">
            <div class="campo-grupo">
                <label for="cedula" class="campo-etiqueta">Cédula (10 dígitos) *</label>
                <input type="text" id="cedula" name="cedula" maxlength="10" class="campo-control input-solo-numeros" placeholder="1712345678" required>
            </div>
            <div class="campo-grupo">
                <label for="telefono" class="campo-etiqueta">Teléfono (10 dígitos) *</label>
                <input type="text" id="telefono" name="telefono" maxlength="10" class="campo-control input-solo-numeros" placeholder="0987654321" required>
            </div>
        </div>

        <div class="campo-grupo">
            <label for="reg_usuario" class="campo-etiqueta">Nombre de Usuario *</label>
            <input type="text" id="reg_usuario" name="usuario" class="campo-control" placeholder="juan_perez" required>
        </div>

        <div class="fila-campos">
            <div class="campo-grupo">
                <label for="password" class="campo-etiqueta">Contraseña *</label>
                <input type="password" id="password" name="password" minlength="6" class="campo-control" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="campo-grupo">
                <label for="confirmar_password" class="campo-etiqueta">Confirmar Contraseña *</label>
                <input type="password" id="confirmar_password" name="confirmar_password" minlength="6" class="campo-control" placeholder="Repita la contraseña" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 10px;">
            Registrarme como Paciente
        </button>
    </form>

    <div class="auth-enlaces">
        ¿Ya tiene una cuenta creada? 
        <a href="index.php?ruta=login">Iniciar sesión</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

