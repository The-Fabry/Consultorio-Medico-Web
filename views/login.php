<?php
/**
 * login.php
 * Para qué sirve: Pantalla para ingresar al sistema con usuario y contraseña.
 * Trabaja con: controllers/AuthController.php, views/header.php y views/footer.php.
 * Equivale en NetBeans a: Formulario de Login.java
 */
require_once __DIR__ . '/header.php';
?>

<div class="tarjeta-auth">
    <div class="auth-cabecera">
        <h2>SmartMed Web</h2>
        <p>Ingrese sus credenciales para acceder al consultorio</p>
    </div>

    <form action="index.php?ruta=login" method="POST" autocomplete="off">
        <div class="campo-grupo">
            <label for="usuario" class="campo-etiqueta">Usuario</label>
            <input type="text" id="usuario" name="usuario" class="campo-control" placeholder="Ej: dr_gomez o juan_perez" required autofocus>
        </div>

        <div class="campo-grupo">
            <label for="password" class="campo-etiqueta">Contraseña</label>
            <input type="password" id="password" name="password" class="campo-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 10px;">
            Iniciar Sesión
        </button>
    </form>

    <div class="auth-enlaces">
        ¿Es un nuevo paciente? 
        <a href="index.php?ruta=registro">Crear cuenta aquí</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

