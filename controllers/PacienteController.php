<?php
/**
 * PacienteController.php
 * Para qué sirve: Gestiona el agendamiento, visualización de tickets y cancelación de citas del paciente.
 * Trabaja con: models/Cita.php, models/Cliente.php, models/Especialista.php y views/paciente/.
 * Equivale en NetBeans a: VistaPaciente.java
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Especialista.php';
require_once __DIR__ . '/../models/Receta.php';

class PacienteController {

    private Cita $citaModelo;
    private Cliente $clienteModelo;
    private Especialista $especialistaModelo;
    private Receta $recetaModelo;

    public function __construct() {
        // Solo usuarios con rol paciente pueden acceder a este controlador
        requerirRol('paciente');
        $this->citaModelo = new Cita();
        $this->clienteModelo = new Cliente();
        $this->especialistaModelo = new Especialista();
        $this->recetaModelo = new Receta();
    }

    // Muestra el formulario o procesa el agendamiento de una nueva cita.
    // Recibe: datos por POST o nada por GET. Devuelve: vista o redirección a ticket.
    public function agendar(): void {
        $usuario = usuarioActual();
        $cliente = $this->clienteModelo->buscarPorCedula($usuario['cedula']);

        // Si el paciente aún no está en la tabla clientes, se sincroniza automáticamente
        if (!$cliente) {
            $idCliente = $this->clienteModelo->sincronizar([
                'cedula'   => $usuario['cedula'],
                'nombre'   => $usuario['nombre'],
                'telefono' => $usuario['telefono']
            ]);
            $cliente = $this->clienteModelo->buscarPorId($idCliente);
        }

        // Si se envió el formulario para guardar la cita
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idEspecialista = (int)($_POST['id_especialista'] ?? 0);
            $fecha = trim($_POST['fecha'] ?? '');
            $hora = trim($_POST['hora'] ?? '');

            if (!$idEspecialista || empty($fecha) || empty($hora)) {
                guardarFlash('error', 'Seleccione el especialista, la fecha y el horario.');
                header('Location: index.php?ruta=paciente/agendar');
                exit;
            }

            $fechaHora = "{$fecha} {$hora}:00";

            // Validar que la hora seleccionada pertenezca a las franjas permitidas:
            // Mañana: 07:00 a 12:00 (cada 30 min)
            // Tarde:  13:30 a 19:00 (cada 30 min)
            $horariosPermitidos = [
                '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00',
                '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'
            ];
            if (!in_array($hora, $horariosPermitidos, true)) {
                guardarFlash('error', 'El horario seleccionado no es válido. Los horarios disponibles son: Mañana de 07:00 a 12:00 y Tarde de 13:30 a 19:00.');
                header('Location: index.php?ruta=paciente/agendar');
                exit;
            }

            // Validar que no sea fecha u hora pasada
            if (strtotime($fechaHora) < time()) {
                guardarFlash('error', 'No puede agendar una cita en una fecha u hora pasada.');
                header('Location: index.php?ruta=paciente/agendar');
                exit;
            }

            // Validar en el servidor que el médico esté libre a esa hora
            if ($this->citaModelo->existeCitaEnHorario($idEspecialista, $fechaHora)) {
                guardarFlash('error', 'El especialista ya tiene una cita reservada a esa hora.');
                header('Location: index.php?ruta=paciente/agendar');
                exit;
            }

            // Generar código de barras: CITA-<cédula>-<4 dígitos aleatorios>
            $random = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $codigoBarras = "CITA-{$usuario['cedula']}-{$random}";

            $idCita = $this->citaModelo->guardarCita($cliente['id_cliente'], $idEspecialista, $fechaHora, $codigoBarras);

            guardarFlash('exito', '¡Cita médica agendada exitosamente!');
            header('Location: index.php?ruta=paciente/ticket&id=' . $idCita);
            exit;
        }

        $especialistas = $this->especialistaModelo->listarTodos();
        $proximaCita = $this->citaModelo->obtenerProximaCita($cliente['id_cliente']);
        $titulo = 'Agendar Cita Médica';
        require_once __DIR__ . '/../views/paciente/agendar.php';
    }

    // Devuelve en formato JSON los horarios ocupados por un médico para la fecha elegida (AJAX).
    // Recibe: id_especialista y fecha por GET. Devuelve: JSON con horas ocupadas.
    public function horariosOcupados(): void {
        $idEspecialista = (int)($_GET['id_especialista'] ?? 0);
        $fecha = $_GET['fecha'] ?? '';

        $ocupadas = ($idEspecialista && $fecha) 
            ? $this->citaModelo->obtenerHorasOcupadas($idEspecialista, $fecha) 
            : [];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ocupadas' => $ocupadas]);
        exit;
    }

    // Muestra la lista de citas médicas agendadas por el paciente.
    // Recibe: nada. Devuelve: vista mis_citas.php.
    public function misCitas(): void {
        $usuario = usuarioActual();
        $cliente = $this->clienteModelo->buscarPorCedula($usuario['cedula']);
        $citas = $cliente ? $this->citaModelo->listarPorCliente($cliente['id_cliente']) : [];

        $titulo = 'Mis Citas Médicas';
        require_once __DIR__ . '/../views/paciente/mis_citas.php';
    }

    // Muestra el ticket digital con código de barras de la cita médica.
    // Recibe: id de la cita por GET. Devuelve: vista ticket.php.
    public function verTicket(): void {
        $idCita = (int)($_GET['id'] ?? 0);
        $usuario = usuarioActual();
        $cita = $this->citaModelo->buscarPorId($idCita);

        // Se valida que la cita pertenezca al paciente que inició sesión
        if (!$cita || $cita['cedula_cliente'] !== $usuario['cedula']) {
            guardarFlash('error', 'Ticket no encontrado o no tiene permiso para verlo.');
            header('Location: index.php?ruta=paciente/citas');
            exit;
        }

        $titulo = 'Ticket de Cita Médica';
        require_once __DIR__ . '/../views/paciente/ticket.php';
    }

    // Cancela una cita médica que se encuentre en estado Pendiente.
    // Recibe: id de la cita por POST. Devuelve: redirección a mis citas.
    public function cancelar(): void {
        $idCita = (int)($_POST['id_cita'] ?? 0);
        $usuario = usuarioActual();
        $cliente = $this->clienteModelo->buscarPorCedula($usuario['cedula']);
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita || !$cliente || (int)$cita['id_cliente'] !== (int)$cliente['id_cliente']) {
            guardarFlash('error', 'No se puede cancelar una cita que no le pertenece.');
            header('Location: index.php?ruta=paciente/citas');
            exit;
        }

        if ($cita['estado'] !== 'Pendiente') {
            guardarFlash('error', 'Solo se pueden cancelar citas en estado Pendiente.');
            header('Location: index.php?ruta=paciente/citas');
            exit;
        }

        if (strtotime($cita['fecha_cita']) < time()) {
            guardarFlash('error', 'No se puede cancelar una cita cuya hora ya pasó.');
            header('Location: index.php?ruta=paciente/citas');
            exit;
        }

        $this->citaModelo->cancelarCita($idCita, $cliente['id_cliente']);
        guardarFlash('exito', 'Cita cancelada con éxito. El horario quedó disponible.');
        header('Location: index.php?ruta=paciente/citas');
        exit;
    }

    // Muestra las recetas emitidas al paciente logueado.
    // Recibe: nada. Devuelve: vista mis_recetas.php.
    public function misRecetas(): void {
        $usuario = usuarioActual();
        $cliente = $this->clienteModelo->buscarPorCedula($usuario['cedula']);
        $recetas = $cliente ? $this->recetaModelo->listarPorCliente($cliente['id_cliente']) : [];

        $titulo = 'Mis Recetas Médicas';
        require_once __DIR__ . '/../views/paciente/mis_recetas.php';
    }
}

