<?php
/**
 * Cliente.php
 * Para qué sirve: Gestiona las consultas de la tabla clientes (pacientes del consultorio).
 * Trabaja con: config/conexion.php y controllers/PacienteController.php.
 * Equivale en NetBeans a: ClienteDAO.java
 */

require_once __DIR__ . '/../config/conexion.php';

class Cliente {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Busca un cliente por su número de cédula.
    // Recibe: cédula de 10 dígitos. Devuelve: arreglo de datos o null.
    public function buscarPorCedula(string $cedula): ?array {
        $sql = "SELECT * FROM clientes WHERE cedula = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Busca un cliente por su id interno.
    // Recibe: id_cliente. Devuelve: arreglo de datos o null.
    public function buscarPorId(int $id): ?array {
        $sql = "SELECT * FROM clientes WHERE id_cliente = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Busca un cliente por el id del usuario registrado (enlace por cédula de identidad).
    // Para la defensa: En este sistema, la cuenta de usuario y la ficha del cliente comparten la cédula.
    // Recibe: id_usuario. Devuelve: arreglo de datos del cliente o null.
    public function buscarPorIdUsuario(int $idUsuario): ?array {
        $sql = "SELECT c.* FROM clientes c
                INNER JOIN usuarios u ON u.cedula = c.cedula
                WHERE u.id_usuario = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Guarda o actualiza los datos del cliente al agendar si no existía.
    // Recibe: cédula, nombre y teléfono. Devuelve: id_cliente.
    public function sincronizar(array $datos): int {
        $sql = "INSERT INTO clientes (cedula, nombre, telefono) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), telefono = VALUES(telefono)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$datos['cedula'], $datos['nombre'], $datos['telefono']]);

        $existente = $this->buscarPorCedula($datos['cedula']);
        return $existente ? (int)$existente['id_cliente'] : (int)$this->pdo->lastInsertId();
    }
}

