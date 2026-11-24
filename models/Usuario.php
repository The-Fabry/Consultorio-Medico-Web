<?php
/**
 * Usuario.php
 * Para qué sirve: Realiza las consultas a la tabla usuarios (buscar, verificar y registrar).
 * Trabaja con: config/conexion.php y controllers/AuthController.php.
 * Equivale en NetBeans a: UsuarioDAO.java
 */

require_once __DIR__ . '/../config/conexion.php';

class Usuario {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Busca un usuario por su nombre de usuario.
    // Recibe: nombre de usuario (string). Devuelve: arreglo con sus datos o null.
    public function buscarPorUsuario(string $usuario): ?array {
        // Se usa consulta preparada con ? para evitar inyección SQL
        $sql = "SELECT * FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Comprueba si un nombre de usuario ya existe para no repetirlo.
    // Recibe: nombre de usuario. Devuelve: true si ya existe.
    public function existeUsuario(string $usuario): bool {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Comprueba si una cédula ya está registrada en el sistema.
    // Recibe: cédula de 10 dígitos. Devuelve: true si ya existe.
    public function existeCedula(string $cedula): bool {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE cedula = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Registra un nuevo paciente desde la pantalla de crear cuenta.
    // Recibe: arreglo con usuario, clave hasheada, cédula, nombre y teléfono. Devuelve: id generado.
    public function registrarPaciente(array $datos): int {
        // El rol siempre queda fijado en 'paciente'
        $sql = "INSERT INTO usuarios (usuario, password, rol, cedula, nombre, telefono, activo) 
                VALUES (?, ?, 'paciente', ?, ?, ?, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['usuario'],
            $datos['password'],
            $datos['cedula'],
            $datos['nombre'],
            $datos['telefono']
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}

