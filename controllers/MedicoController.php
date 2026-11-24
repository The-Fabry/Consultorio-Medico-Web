<?php
/**
 * MedicoController.php
 * Para qué sirve: Administra la agenda médica, atención de consultas y prescripción de recetas.
 * Trabaja con: models/Cita.php, models/HistorialClinico.php, models/Receta.php y views/medico/.
 * Equivale en NetBeans a: VistaMedico.java y PanelHistorialMedico.java
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Especialista.php';
require_once __DIR__ . '/../models/HistorialClinico.php';
require_once __DIR__ . '/../models/Receta.php';

class MedicoController {

    private Cita $citaModelo;
    private Especialista $especialistaModelo;
    private HistorialClinico $historialModelo;
    private Receta $recetaModelo;

    public function __construct() {
        $this->citaModelo = new Cita();
        $this->especialistaModelo = new Especialista();
        $this->historialModelo = new HistorialClinico();
        $this->recetaModelo = new Receta();
    }

    // Muestra la agenda personal del médico logueado.
    // Recibe: fecha y estado opcionales por GET. Devuelve: vista agenda.php.
    public function agenda(): void {
        requerirRol('medico');
        $usuario = usuarioActual();
        $especialista = $this->especialistaModelo->buscarPorCedula($usuario['cedula']);
        $idEspecialista = $especialista ? (int)$especialista['id_especialista'] : 1;

        $fechaFiltro = $_GET['fecha'] ?? '';
        $estadoFiltro = $_GET['estado'] ?? '';

        $citas = $this->citaModelo->listarPorMedico($idEspecialista, $fechaFiltro ?: null, $estadoFiltro ?: null);
        $titulo = 'Mi Agenda Médica';
        require_once __DIR__ . '/../views/medico/agenda.php';
    }

    // Muestra la pantalla de atención médica (solo para citas En Proceso).
    // Recibe: id de la cita por GET. Devuelve: vista atencion.php.
    public function atencion(): void {
        requerirRol('medico');
        $idCita = (int)($_GET['id'] ?? 0);
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita) {
            guardarFlash('error', 'Cita médica no encontrada.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        // Si la cita aún está Pendiente, se avisa que recepción no ha registrado la llegada
        if ($cita['estado'] === 'Pendiente') {
            guardarFlash('error', 'La cita aún está Pendiente. Recepción debe registrar la llegada del paciente.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        // Si se envió el formulario para guardar el diagnóstico médico
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diagnostico = trim($_POST['diagnostico'] ?? '');
            $notas = trim($_POST['notas'] ?? '');

            if (empty($diagnostico)) {
                guardarFlash('error', 'El diagnóstico médico es obligatorio.');
                header('Location: index.php?ruta=medico/atencion&id=' . $idCita);
                exit;
            }

            $this->historialModelo->guardarAtencion($idCita, $diagnostico, $notas);
            guardarFlash('exito', 'Atención registrada con éxito. La cita quedó Finalizada.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        $consultasAnteriores = $this->historialModelo->listarPorCliente($cita['id_cliente']);
        $historialActual = $this->historialModelo->buscarPorCita($idCita);
        $titulo = 'Atención Médica - Cita #' . $cita['id_cita'];
        require_once __DIR__ . '/../views/medico/atencion.php';
    }

    // Muestra el formulario para prescribir medicamentos a una cita atendida.
    // Recibe: id_cita por GET. Devuelve: vista receta_form.php.
    public function recetaForm(): void {
        requerirRol('medico');
        $idCita = (int)($_GET['id_cita'] ?? 0);
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita || $cita['estado'] === 'Pendiente' || $cita['estado'] === 'Cancelada') {
            guardarFlash('error', 'Solo puede prescribir recetas en citas en atención o finalizadas.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        $titulo = 'Emitir Receta Médica';
        require_once __DIR__ . '/../views/medico/receta_form.php';
    }

    // Guarda la receta médica y sus medicamentos en la base de datos.
    // Recibe: id_cita y arreglos de medicamentos por POST. Devuelve: redirección a receta imprimible.
    public function guardarReceta(): void {
        requerirRol('medico');
        $idCita = (int)($_POST['id_cita'] ?? 0);
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita) {
            guardarFlash('error', 'Cita médica no válida.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        $nombres = $_POST['medicamento'] ?? [];
        $dosis = $_POST['dosis'] ?? [];
        $frecuencias = $_POST['frecuencia'] ?? [];
        $duraciones = $_POST['duracion'] ?? [];
        $vias = $_POST['via_administracion'] ?? [];
        $indicaciones = trim($_POST['indicaciones'] ?? '');

        // Validar que exista al menos un medicamento completo
        $medsValidos = [];
        for ($i = 0; $i < count($nombres); $i++) {
            $nom = trim($nombres[$i] ?? '');
            if ($nom !== '') {
                $medsValidos[] = [
                    'medicamento'        => $nom,
                    'dosis'              => trim($dosis[$i] ?? ''),
                    'frecuencia'         => trim($frecuencias[$i] ?? ''),
                    'duracion'           => trim($duraciones[$i] ?? ''),
                    'via_administracion' => trim($vias[$i] ?? 'Oral')
                ];
            }
        }

        if (empty($medsValidos)) {
            guardarFlash('error', 'Debe ingresar al menos un medicamento con su dosis y frecuencia.');
            header('Location: index.php?ruta=medico/receta&id_cita=' . $idCita);
            exit;
        }

        // Generar código único de receta: REC-AAAAMMDD-####
        $fecha = date('Ymd');
        $random = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $codigoReceta = "REC-{$fecha}-{$random}";

        $idReceta = $this->recetaModelo->guardarConMedicamentos(
            $cita['id_cita'],
            $cita['id_cliente'],
            $cita['id_especialista'],
            $codigoReceta,
            $indicaciones,
            $medsValidos
        );

        guardarFlash('exito', '¡Receta médica emitida con éxito!');
        header('Location: index.php?ruta=medico/receta_imprimible&id=' . $idReceta);
        exit;
    }

    // Muestra la hoja de receta médica imprimible con código de barras y firma.
    // Recibe: id de la receta por GET. Devuelve: vista receta_imprimible.php.
    public function verReceta(): void {
        // Puede verla tanto el médico como el paciente que inició sesión
        requerirRol(['medico', 'paciente']);
        $idReceta = (int)($_GET['id'] ?? 0);
        $receta = $this->recetaModelo->buscarPorId($idReceta);

        if (!$receta) {
            guardarFlash('error', 'Receta médica no encontrada.');
            header('Location: index.php?ruta=login');
            exit;
        }

        $titulo = 'Receta Médica ' . $receta['codigo_receta'];
        require_once __DIR__ . '/../views/medico/receta_imprimible.php';
    }
}

