<?php
/**
 * login.php
 * Para qué sirve: Pantalla de inicio de sesión con diseño moderno de panel dividido (Split-Screen Login).
 * Trabaja con: controllers/AuthController.php, assets/css/estilos.css y assets/logo.png.
 * Equivale en NetBeans a: Formulario de Login.java
 * 
 * Estructura de la vista:
 * - Panel Izquierdo (Branding y Bienvenida): Gradiente clínico institucional, logotipo oficial de SmartMed Web,
 *   mensaje de bienvenida y eslogan médico.
 * - Panel Derecho (Formulario de Acceso): Campos limpios para Usuario y Contraseña, selector rápido de perfil,
 *   casilla de recordatorio y botón de acceso directo.
 */
require_once __DIR__ . '/header.php';
?>

<!-- Contenedor general centrado de pantalla dividida (Split-Screen) -->
<div class="login-split-contenedor">
    <div class="login-split-tarjeta">
        
        <!-- PANEL IZQUIERDO: Bienvenida, Marca e Identidad Clínica -->
        <div class="login-panel-marca">
            <div class="login-marca-fondo-formas"></div>
            
            <div class="login-marca-contenido">
                <!-- Logotipo de SmartMed Web -->
                <div class="login-marca-logo">
                    <img src="assets/logo.png" alt="Logo SmartMed Web" class="login-logo-img">
                </div>

                <!-- Mensaje principal de bienvenida -->
                <div class="login-marca-texto">
                    <span class="login-marca-subtitulo-tag">PORTAL CLÍNICO DIGITAL</span>
                    <h1 class="login-marca-titulo">Bienvenido a<br><span class="login-marca-destaque">SmartMed Web</span></h1>
                    <p class="login-marca-descripcion">
                        Sistema integral de gestión para consultorios médicos. Agendamiento ágil de citas, prescripción de recetas electrónicas y atención clínica eficiente y segura.
                    </p>
                </div>

                <!-- Detalle de apoyo clínico inferior -->
                <div class="login-marca-pie">
                    <div class="login-marca-icono">🩺</div>
                    <div>
                        <strong>Cuidado médico inteligente a tu alcance</strong>
                        <p>Atención médica organizada con tecnología web moderna.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO: Formulario de Inicio de Sesión -->
        <div class="login-panel-formulario">
            <div class="login-formulario-interior">
                
                <div class="login-form-cabecera">
                    <div class="login-form-badge">ACCESO SEGURO</div>
                    <h2>Iniciar Sesión</h2>
                    <p>Ingrese sus credenciales para acceder al consultorio</p>
                </div>

                <form action="index.php?ruta=login" method="POST" autocomplete="off" class="login-form-cuerpo">
                    
                    <!-- Campo: Usuario -->
                    <div class="campo-grupo">
                        <label for="usuario" class="campo-etiqueta">
                            <span class="icono-input">👤</span> Usuario o Correo
                        </label>
                        <div class="input-con-icono">
                            <input type="text" id="usuario" name="usuario" class="campo-control login-input" placeholder="Ej: dr_gomez o juan_perez" required autofocus>
                        </div>
                    </div>

                    <!-- Campo: Contraseña -->
                    <div class="campo-grupo">
                        <label for="password" class="campo-etiqueta">
                            <span class="icono-input">🔒</span> Contraseña
                        </label>
                        <div class="input-con-icono">
                            <input type="password" id="password" name="password" class="campo-control login-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <!-- Selector visual de Perfil / Rol sugerido -->
                    <div class="campo-grupo" style="margin-bottom: 14px;">
                        <label class="campo-etiqueta" style="font-size: 0.82rem; color: #64748B;">
                            Tipo de Perfil de Acceso:
                        </label>
                        <div class="login-roles-selector">
                            <label class="login-rol-item">
                                <input type="radio" name="rol_vista" value="paciente" checked>
                                <span class="login-rol-boton">👤 Paciente</span>
                            </label>
                            <label class="login-rol-item">
                                <input type="radio" name="rol_vista" value="medico">
                                <span class="login-rol-boton">🩺 Médico</span>
                            </label>
                            <label class="login-rol-item">
                                <input type="radio" name="rol_vista" value="recepcion">
                                <span class="login-rol-boton">📋 Recepción</span>
                            </label>
                        </div>
                    </div>

                    <!-- Opciones adicionales: Recordarme -->
                    <div class="login-form-opciones">
                        <label class="login-checkbox-label">
                            <input type="checkbox" name="recordarme" value="1" checked>
                            <span>Recordar mi sesión en este equipo</span>
                        </label>
                    </div>

                    <!-- Botón de Acción Principal -->
                    <button type="submit" class="btn btn-login-ingresar">
                        INGRESAR AL SISTEMA ➔
                    </button>
                </form>

                <!-- Pie de autenticación: Enlace de registro para pacientes -->
                <div class="login-form-pie">
                    <span>¿Es un nuevo paciente?</span>
                    <a href="index.php?ruta=registro" class="login-enlace-registro">
                        Crear cuenta aquí
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

