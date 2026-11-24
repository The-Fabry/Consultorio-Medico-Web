<?php
/**
 * HistorialClinico.php
 * Para qué sirve: Administra los diagnósticos y notas médicas registradas en las consultas.
 * Trabaja con: config/conexion.php y controllers/MedicoController.php / ReporteController.php.
 * Equivale en NetBeans a: HistorialClinicoDAO.java
 */

require_once __DIR__ . '/../config/conexion.php';

class HistorialClinico {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Guarda el diagnóstico y marca la cita como Finalizada dentro de una transacción.
    // Recibe: idCita, diagnostico y notas. Devuelve: true si se guardó.
    public function guardarAtencion(int $idCita, string $diagnostico, ?string $notas): bool {
        try {
            $this->pdo->beginTransaction();

            // 1. Guardar o actualizar el diagnóstico en historial_clinico
            $sqlHistorial = "INSERT INTO historial_clinico (id_cita, diagnostico, notas, fecha_registro)
                             VALUES (?, ?, ?, NOW())
                             ON DUPLICATE KEY UPDATE 
                                diagnostico = VALUES(diagnostico),
                                notas = VALUES(notas),
                                fecha_registro = NOW()";
            $stmtH = $this->pdo->prepare($sqlHistorial);
            $stmtH->execute([$idCita, $diagnostico, $notas]);

            // 2. Cambiar el estado de la cita a Finalizada
            $sqlCita = "UPDATE citas SET estado = 'Finalizada' WHERE id_cita = ?";
            $stmtC = $this->pdo->prepare($sqlCita);
            $stmtC->execute([$idCita]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Lista las consultas médicas anteriores realizadas a un cliente.
    // Recibe: id_cliente. Devuelve: arreglo de consultas previas.
    public function listarPorCliente(int $idCliente): array {
        $sql = "SELECT h.*, c.fecha_cita, e.nombre_completo AS nombre_especialista, e.especialidad
                FROM historial_clinico h
                INNER JOIN citas c ON h.id_cita = c.id_cita
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE c.id_cliente = ? ORDER BY c.fecha_cita DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCliente]);
        return $stmt->fetchAll();
    }

    // Busca el registro de diagnóstico de una cita en particular.
    // Recibe: id_cita. Devuelve: datos de historial o null.
    public function buscarPorCita(int $idCita): ?array {
        $sql = "SELECT * FROM historial_clinico WHERE id_cita = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCita]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}

