<?php
/**
 * Receta.php
 * Para qué sirve: Realiza las operaciones en la base de datos de recetas médicas y medicamentos.
 * Trabaja con: config/conexion.php y controllers/MedicoController.php / PacienteController.php.
 * Equivale en NetBeans a: No existía en la app de escritorio (NUEVA función web).
 */

require_once __DIR__ . '/../config/conexion.php';

class Receta {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Guarda una receta con su lista de medicamentos en una transacción.
    // Recibe: datos de cabecera y arreglo de medicamentos. Devuelve: id_receta generado.
    public function guardarConMedicamentos(int $idCita, int $idCliente, int $idEspecialista, string $codigoReceta, ?string $indicaciones, array $medicamentos): int {
        try {
            $this->pdo->beginTransaction();

            // 1. Guardar la cabecera de la receta
            $sqlReceta = "INSERT INTO recetas (id_cita, id_cliente, id_especialista, codigo_receta, indicaciones, fecha_emision)
                          VALUES (?, ?, ?, ?, ?, NOW())";
            $stmtR = $this->pdo->prepare($sqlReceta);
            $stmtR->execute([$idCita, $idCliente, $idEspecialista, $codigoReceta, $indicaciones]);
            $idReceta = (int)$this->pdo->lastInsertId();

            // 2. Guardar cada medicamento prescrito
            $sqlMed = "INSERT INTO receta_medicamentos (id_receta, medicamento, dosis, frecuencia, duracion, via_administracion)
                       VALUES (?, ?, ?, ?, ?, ?)";
            $stmtM = $this->pdo->prepare($sqlMed);

            foreach ($medicamentos as $med) {
                $stmtM->execute([
                    $idReceta,
                    $med['medicamento'],
                    $med['dosis'],
                    $med['frecuencia'],
                    $med['duracion'],
                    $med['via_administracion'] ?? 'Oral'
                ]);
            }

            $this->pdo->commit();
            return $idReceta;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Busca una receta completa por su id junto a los datos del paciente y médico.
    // Recibe: idReceta. Devuelve: arreglo con la receta y sus medicamentos.
    public function buscarPorId(int $idReceta): ?array {
        $sql = "SELECT r.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente, cl.telefono,
                       e.nombre_completo AS nombre_especialista, e.especialidad, e.cedula AS cedula_especialista,
                       c.fecha_cita
                FROM recetas r
                INNER JOIN clientes cl ON r.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON r.id_especialista = e.id_especialista
                INNER JOIN citas c ON r.id_cita = c.id_cita
                WHERE r.id_receta = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idReceta]);
        $receta = $stmt->fetch();

        if ($receta) {
            $receta['medicamentos'] = $this->listarMedicamentos($idReceta);
        }
        return $receta ?: null;
    }

    // Lista todas las recetas emitidas a un paciente.
    // Recibe: id_cliente. Devuelve: arreglo de recetas emitidas.
    public function listarPorCliente(int $idCliente): array {
        $sql = "SELECT r.*, e.nombre_completo AS nombre_especialista, e.especialidad, c.fecha_cita
                FROM recetas r
                INNER JOIN especialistas e ON r.id_especialista = e.id_especialista
                INNER JOIN citas c ON r.id_cita = c.id_cita
                WHERE r.id_cliente = ? ORDER BY r.fecha_emision DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCliente]);
        return $stmt->fetchAll();
    }

    // Obtiene los medicamentos de una receta.
    // Recibe: id_receta. Devuelve: arreglo de medicamentos.
    public function listarMedicamentos(int $idReceta): array {
        $sql = "SELECT * FROM receta_medicamentos WHERE id_receta = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idReceta]);
        return $stmt->fetchAll();
    }

    // Busca una receta asociada directamente a una cita médica.
    // Recibe: id_cita. Devuelve: datos de la receta con sus medicamentos o null.
    public function buscarPorCita(int $idCita): ?array {
        $sql = "SELECT r.*, e.nombre_completo AS nombre_especialista, e.especialidad
                FROM recetas r
                INNER JOIN especialistas e ON r.id_especialista = e.id_especialista
                WHERE r.id_cita = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCita]);
        $receta = $stmt->fetch();

        if ($receta) {
            $receta['medicamentos'] = $this->listarMedicamentos((int) $receta['id_receta']);
        }
        return $receta ?: null;
    }

    // Actualiza las indicaciones y reemplaza los medicamentos de una receta existente.
    // Usa una transacción PDO para asegurar consistencia (si falla uno, no se guarda nada).
    public function actualizarConMedicamentos(int $idReceta, ?string $indicaciones, array $medicamentos): bool {
        try {
            $this->pdo->beginTransaction();

            // 1. Actualizar indicaciones generales de la receta
            $stmt = $this->pdo->prepare("UPDATE recetas SET indicaciones = ? WHERE id_receta = ?");
            $stmt->execute([$indicaciones, $idReceta]);

            // 2. Eliminar medicamentos previos para reemplazarlos con la nueva prescripción
            $stmtDel = $this->pdo->prepare("DELETE FROM receta_medicamentos WHERE id_receta = ?");
            $stmtDel->execute([$idReceta]);

            // 3. Insertar la nueva lista de medicamentos
            $stmtMed = $this->pdo->prepare(
                "INSERT INTO receta_medicamentos (id_receta, medicamento, dosis, frecuencia, duracion, via_administracion)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            foreach ($medicamentos as $med) {
                $nombreMed = trim($med['medicamento'] ?? '');
                if ($nombreMed === '') {
                    continue; // Omitir líneas vacías
                }
                $stmtMed->execute([
                    $idReceta,
                    $nombreMed,
                    $med['dosis'] ?? 'Según indicación',
                    $med['frecuencia'] ?? 'Ver posología',
                    $med['duracion'] ?? 'Tratamiento',
                    $med['via_administracion'] ?? 'Oral'
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}

