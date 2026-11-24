<?php
/**
 * conexion.php
 * Para qué sirve: Realiza la conexión a la base de datos MySQL usando PDO.
 * Trabaja con: Todos los modelos dentro de la carpeta models/.
 * Equivale en NetBeans a: ConexionBD.java
 */

// Se fija la zona horaria oficial de Ecuador
date_default_timezone_set('America/Guayaquil');

// Crea y devuelve una conexión activa a MySQL con PDO.
// Recibe: nada. Devuelve: objeto PDO listo para ejecutar consultas.
function conectarBD(): PDO {
    $servidor = '127.0.0.1';
    $baseDatos = 'consultorio_dbw';
    $usuario = 'root';
    $clave = '';

    try {
        // Se conecta con codificación UTF-8 para soportar tildes y eñes
        $pdo = new PDO("mysql:host={$servidor};dbname={$baseDatos};charset=utf8mb4", $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Avisa los errores de SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Devuelve arreglos asociativos
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error al conectar con la base de datos: " . $e->getMessage());
    }
}

