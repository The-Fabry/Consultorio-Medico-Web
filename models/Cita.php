<?php
/**
 * Cita.php
 * Para qué sirve: Realiza todas las consultas SQL sobre la tabla citas.
 * Trabaja con: PacienteController, RecepcionController, MedicoController y ReporteController.
 * Equivale en NetBeans a: CitaDAO.java
 */

require_once __DIR__ . '/../config/conexion.php';

class Cita {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = conectarBD();
    }

    // Guarda una nueva cita médica con estado 'Pendiente'.
    // Recibe: idCliente, idEspecialista, fechaCita y código de barras. Devuelve: id_cita generado.
    public function guardarCita(int $idCliente, int $idEspecialista, string $fechaCita, string $codigoBarras): int {
        $sql = "INSERT INTO citas (id_cliente, id_especialista, fecha_cita, codigo_barras, estado, creado_en)
                VALUES (?, ?, ?, ?, 'Pendiente', NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCliente, $idEspecialista, $fechaCita, $codigoBarras]);
        return (int)$this->pdo->lastInsertId();
    }

    // Comprueba si un especialista ya tiene una cita reservada a esa fecha y hora.
    // Recibe: idEspecialista y fechaHora. Devuelve: true si ya está ocupado.
    public function existeCitaEnHorario(int $idEspecialista, string $fechaHora): bool {
        // Se omiten las citas con estado Cancelada porque ese turno queda libre
        $sql = "SELECT COUNT(*) FROM citas 
                WHERE id_especialista = ? AND fecha_cita = ? AND estado != 'Cancelada'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idEspecialista, $fechaHora]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Devuelve un arreglo con las horas (H:i) ya ocupadas para un médico en una fecha.
    // Recibe: idEspecialista y fecha (Y-m-d). Devuelve: arreglo de horas ocupadas.
    public function obtenerHorasOcupadas(int $idEspecialista, string $fecha): array {
        $sql = "SELECT DATE_FORMAT(fecha_cita, '%H:%i') FROM citas 
                WHERE id_especialista = ? AND DATE(fecha_cita) = ? AND estado != 'Cancelada'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idEspecialista, $fecha]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Obtiene la próxima cita médica en estado Pendiente del paciente.
    // Recibe: id_cliente. Devuelve: datos de la cita o null si no tiene.
    public function obtenerProximaCita(int $idCliente): ?array {
        $sql = "SELECT c.*, e.nombre_completo AS nombre_especialista, e.especialidad 
                FROM citas c
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE c.id_cliente = ? AND c.estado = 'Pendiente' AND c.fecha_cita >= NOW()
                ORDER BY c.fecha_cita ASC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCliente]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Lista todas las citas agendadas por un paciente.
    // Recibe: id_cliente. Devuelve: arreglo de citas con datos del médico.
    public function listarPorCliente(int $idCliente): array {
        $sql = "SELECT c.*, e.nombre_completo AS nombre_especialista, e.especialidad 
                FROM citas c
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE c.id_cliente = ? ORDER BY c.fecha_cita DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCliente]);
        return $stmt->fetchAll();
    }

    // Busca una cita por su id con los datos de paciente y especialista.
    // Recibe: id_cita. Devuelve: datos de la cita o null.
    public function buscarPorId(int $idCita): ?array {
        $sql = "SELECT c.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente, cl.telefono,
                       e.nombre_completo AS nombre_especialista, e.especialidad, e.cedula AS cedula_especialista
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE c.id_cita = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCita]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Busca una cita por su código de barras de manera flexible (con o sin 'CITA-' y con o sin guiones).
    // Recibe: código leído o digitado. Devuelve: datos de la cita o null.
    public function buscarPorCodigo(string $codigo): ?array {
        $cod = trim($codigo);
        // Extrae solo los dígitos para lectores láser que lean en modo CODE_128C
        $soloDigitos = preg_replace('/\D/', '', $cod);
        $sinGuiones = str_replace('-', '', $cod);

        $sql = "SELECT c.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente, cl.telefono,
                       e.nombre_completo AS nombre_especialista, e.especialidad
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE c.codigo_barras = ? 
                   OR REPLACE(c.codigo_barras, '-', '') = ?
                   OR REPLACE(REPLACE(c.codigo_barras, 'CITA-', ''), '-', '') = ?
                   OR c.codigo_barras LIKE ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cod, $sinGuiones, $soloDigitos !== '' ? $soloDigitos : $cod, '%' . $cod . '%']);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Cancela una cita Pendiente del paciente liberando el horario.
    // Recibe: id_cita e id_cliente. Devuelve: true si se canceló.
    public function cancelarCita(int $idCita, int $idCliente): bool {
        $sql = "UPDATE citas SET estado = 'Cancelada', cancelada_en = NOW() 
                WHERE id_cita = ? AND id_cliente = ? AND estado = 'Pendiente' AND fecha_cita >= NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCita, $idCliente]);
        return $stmt->rowCount() > 0;
    }

    // Cambia el estado de una cita médica (ejemplo: a 'En Proceso' o 'Finalizada').
    // Recibe: id_cita y nuevoEstado. Devuelve: bool.
    public function cambiarEstado(int $idCita, string $nuevoEstado): bool {
        $sql = "UPDATE citas SET estado = ? WHERE id_cita = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevoEstado, $idCita]);
    }

    // Lista las citas médicas de un día específico con filtros opcionales.
    // Recibe: fecha (Y-m-d), idEspecialista opcional y estado opcional. Devuelve: arreglo de citas.
    public function listarPorDia(string $fecha, ?int $idEspecialista = null, ?string $estado = null): array {
        $sql = "SELECT c.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente, cl.telefono,
                       e.nombre_completo AS nombre_especialista, e.especialidad
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista
                WHERE DATE(c.fecha_cita) = ?";
        $params = [$fecha];

        if ($idEspecialista) {
            $sql .= " AND c.id_especialista = ?";
            $params[] = $idEspecialista;
        }
        if ($estado) {
            $sql .= " AND c.estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY c.fecha_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lista las citas médicas asignadas a un médico en su agenda.
    // Recibe: idEspecialista, fecha opcional y estado opcional. Devuelve: arreglo de citas.
    public function listarPorMedico(int $idEspecialista, ?string $fecha = null, ?string $estado = null): array {
        $sql = "SELECT c.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente, cl.telefono
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                WHERE c.id_especialista = ?";
        $params = [$idEspecialista];

        if ($fecha) {
            $sql .= " AND DATE(c.fecha_cita) = ?";
            $params[] = $fecha;
        }
        if ($estado) {
            $sql .= " AND c.estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY c.fecha_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

