<?php
/**
 * Especialista.php
 * Para qué sirve: Gestiona la información de los médicos especialistas de la base de datos.
 * Trabaja con: config/conexion.php y controllers/PacienteController.php / MedicoController.php.
 * Equivale en NetBeans a: EspecialistaDAO.java
 */

require_once __DIR__ . '/../config/conexion.php';

class Especialista {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Obtiene la lista completa de médicos especialistas.
    // Recibe: nada. Devuelve: arreglo con todos los especialistas y sus especialidades.
    public function listarTodos(): array {
        $sql = "SELECT * FROM especialistas ORDER BY especialidad ASC, nombre_completo ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Busca un especialista por su identificador primario.
    // Recibe: id_especialista (int). Devuelve: datos del médico o null.
    public function buscarPorId(int $id): ?array {
        $sql = "SELECT * FROM especialistas WHERE id_especialista = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Busca un especialista por su número de cédula (para asociarlo con la sesión).
    // Recibe: cédula del médico. Devuelve: datos del especialista o null.
    public function buscarPorCedula(string $cedula): ?array {
        $sql = "SELECT * FROM especialistas WHERE cedula = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}

