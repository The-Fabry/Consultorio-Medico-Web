<?php
/**
 * ReporteController.php
 * Para qué sirve: Genera los reportes clínicos e históricos en formato web listo para imprimir.
 * Trabaja con: config/conexion.php, models/Cliente.php y views/reportes/.
 * Equivale en NetBeans a: GestorReportes.java (reemplaza JasperReports)
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Especialista.php';

class ReporteController {

    private PDO $pdo;
    private Cliente $clienteModelo;
    private Especialista $especialistaModelo;

    public function __construct() {
        // Médicos, recepcionistas y administradores acceden a los reportes clínicos
        requerirRol(['medico', 'recepcion', 'admin']);
        $this->pdo = conectarBD();
        $this->clienteModelo = new Cliente();
        $this->especialistaModelo = new Especialista();
    }

    // Reporte de citas:
    // - Si el usuario es 'medico', filtra ÚNICAMENTE sus propias citas asignadas.
    // - Si el usuario es 'recepcion' o 'admin', muestra las citas globales de todo el consultorio.
    // Recibe: fecha_inicio y fecha_fin opcionales por GET. Devuelve: vista citas.php.
    public function citas(): void {
        $usuario = usuarioActual();
        $rol = $usuario['rol'] ?? '';
        $especialistaActual = null;

        // Si es médico, identificamos su id_especialista a partir de su cédula
        if ($rol === 'medico') {
            $especialistaActual = $this->especialistaModelo->buscarPorCedula($usuario['cedula'] ?? '');
            if (!$especialistaActual) {
                guardarFlash('error', 'No se encontró un perfil de médico especialista asociado a su cuenta.');
                $citas = [];
                $fechaInicio = '';
                $fechaFin = '';
                $titulo = 'Reporte de Citas Médicas';
                require_once __DIR__ . '/../views/reportes/citas.php';
                return;
            }
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin    = $_GET['fecha_fin'] ?? '';

        $sql = "SELECT c.id_cita, c.fecha_cita, c.codigo_barras, c.estado,
                       cl.nombre AS paciente, cl.cedula, e.nombre_completo AS especialista, e.especialidad
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista";
        $condiciones = [];
        $params = [];

        // Filtro por médico en sesión (si es rol médico)
        if ($rol === 'medico' && $especialistaActual) {
            $condiciones[] = "c.id_especialista = ?";
            $params[] = (int)$especialistaActual['id_especialista'];
        }

        // Si se especificó un rango de fechas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $condiciones[] = "DATE(c.fecha_cita) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY c.fecha_cita DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $citas = $stmt->fetchAll();

        $titulo = 'Reporte de Citas Médicas' . ($especialistaActual ? ' - ' . $especialistaActual['nombre_completo'] : '');
        require_once __DIR__ . '/../views/reportes/citas.php';
    }

    // Reporte de historial clínico: por cédula de paciente o completo si se deja vacío.
    // Recibe: cedula opcional por GET. Devuelve: vista historial_paciente.php.
    public function historial(): void {
        $cedula = trim($_GET['cedula'] ?? '');
        $cliente = null;

        $sql = "SELECT h.*, c.fecha_cita, cl.nombre AS paciente, cl.cedula, cl.telefono,
                       e.nombre_completo AS especialista, e.especialidad
                FROM historial_clinico h
                INNER JOIN citas c ON h.id_cita = c.id_cita
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista";
        $params = [];

        if ($cedula !== '') {
            $cliente = $this->clienteModelo->buscarPorCedula($cedula);
            $sql .= " WHERE cl.cedula = ?";
            $params = [$cedula];
        }

        $sql .= " ORDER BY c.fecha_cita DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $historiales = $stmt->fetchAll();

        $titulo = 'Historial Clínico de Pacientes';
        require_once __DIR__ . '/../views/reportes/historial_paciente.php';
    }
}

