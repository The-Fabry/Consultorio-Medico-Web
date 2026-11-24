<?php
/**
 * sesion.php
 * Para qué sirve: Administra la sesión del usuario y protege las pantallas por rol.
 * Trabaja con: Todos los controladores y las vistas en views/.
 * Equivale en NetBeans a: Sesion.java
 */

// Inicia la sesión de PHP si aún no se ha iniciado.
// Recibe: nada. Devuelve: void.
function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Devuelve los datos del usuario que inició sesión.
// Recibe: nada. Devuelve: arreglo asociativo con los datos o null.
function usuarioActual(): ?array {
    iniciarSesion();
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    return [
        'id'         => $_SESSION['usuario_id'],
        'id_usuario' => $_SESSION['usuario_id'], // Alias de compatibilidad por id
        'usuario'    => $_SESSION['usuario_nombre_usuario'],
        'nombre'     => $_SESSION['usuario_nombre'],
        'rol'        => $_SESSION['usuario_rol'],
        'cedula'     => $_SESSION['usuario_cedula'],
        'telefono'   => $_SESSION['usuario_telefono']
    ];
}

// Alias de compatibilidad: devuelve los datos del usuario autenticado en sesión.
// Para la defensa: Esta función permite que controladores o vistas que llamen a usuarioAutenticado()
// funcionen sin errores, redirigiendo internamente a usuarioActual().
// Recibe: nada. Devuelve: arreglo asociativo con los datos o null.
function usuarioAutenticado(): ?array {
    return usuarioActual();
}

// Bloquea el acceso a una pantalla si el usuario no tiene el rol adecuado.
// Recibe: un rol o un arreglo de roles permitidos. Devuelve: void.
function requerirRol($rolesPermitidos): void {
    iniciarSesion();
    $roles = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];
    $usuario = usuarioActual();

    // Si no ha iniciado sesión, se manda al login
    if (!$usuario) {
        guardarFlash('error', 'Debe iniciar sesión para ingresar.');
        header('Location: index.php?ruta=login');
        exit;
    }

    // Si su rol no está en la lista permitida, se bloquea
    if (!in_array($usuario['rol'], $roles, true)) {
        guardarFlash('error', 'Acceso denegado: no tiene permisos para ver esta pantalla.');
        header('Location: index.php?ruta=login');
        exit;
    }
}

// Guarda un mensaje temporal de alerta para mostrarlo en la siguiente pantalla.
// Recibe: tipo (exito o error) y el texto del mensaje. Devuelve: void.
function guardarFlash(string $tipo, string $mensaje): void {
    iniciarSesion();
    $_SESSION['flash'][$tipo] = $mensaje;
}

// Obtiene el mensaje temporal y lo borra para que no se repita.
// Recibe: tipo (exito o error). Devuelve: texto del mensaje o null.
function obtenerFlash(string $tipo): ?string {
    iniciarSesion();
    if (isset($_SESSION['flash'][$tipo])) {
        $msg = $_SESSION['flash'][$tipo];
        unset($_SESSION['flash'][$tipo]);
        return $msg;
    }
    return null;
}

