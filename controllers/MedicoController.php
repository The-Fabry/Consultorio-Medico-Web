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
    // Solo lista las citas que corresponden al especialista en sesión.
    // Si no se encuentra un especialista asociado a la cédula del usuario, no expone citas de otros.
    public function agenda(): void {
        requerirRol('medico');
        $usuario = usuarioActual();
        $especialista = $this->especialistaModelo->buscarPorCedula($usuario['cedula'] ?? '');

        // Validación de seguridad clínica: si el usuario no tiene ficha de especialista asociada,
        // no se asigna ningún id por defecto (evita ver citas de otros especialistas).
        if (!$especialista) {
            guardarFlash('error', 'No se encontró un perfil de médico especialista asociado a su cuenta.');
            $citas = [];
            $fechaFiltro = '';
            $estadoFiltro = '';
            $titulo = 'Mi Agenda Médica';
            require_once __DIR__ . '/../views/medico/agenda.php';
            return;
        }

        $idEspecialista = (int)$especialista['id_especialista'];
        $fechaFiltro = $_GET['fecha'] ?? '';
        $estadoFiltro = $_GET['estado'] ?? '';

        // Filtrar citas asignadas estrictamente a este médico
        $citas = $this->citaModelo->listarPorMedico($idEspecialista, $fechaFiltro ?: null, $estadoFiltro ?: null);
        $titulo = 'Mi Agenda Médica - ' . $especialista['nombre_completo'];
        require_once __DIR__ . '/../views/medico/agenda.php';
    }

    // Muestra la pantalla de atención médica (solo para citas En Proceso o Finalizadas del médico).
    // Recibe: id de la cita por GET. Devuelve: vista atencion.php.
    public function atencion(): void {
        requerirRol('medico');
        $usuario = usuarioActual();
        $especialista = $this->especialistaModelo->buscarPorCedula($usuario['cedula'] ?? '');

        if (!$especialista) {
            guardarFlash('error', 'No tiene un perfil de médico asociado.');
            header('Location: index.php?ruta=login');
            exit;
        }

        $idCita = (int)($_GET['id'] ?? 0);
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita) {
            guardarFlash('error', 'Cita médica no encontrada.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        // Validación de pertenencia: un médico solo puede atender sus propias citas asignadas
        if ((int)$cita['id_especialista'] !== (int)$especialista['id_especialista']) {
            guardarFlash('error', 'No tiene permisos para atender una cita asignada a otro especialista.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        // Si la cita aún está Pendiente, se avisa que recepción no ha registrado la llegada
        if ($cita['estado'] === 'Pendiente') {
            guardarFlash('error', 'La cita aún está Pendiente. Recepción debe registrar la llegada del paciente.');
            header('Location: index.php?ruta=medico/agenda');
            exit;
        }

        // Si se envió el formulario para guardar el diagnóstico y prescripción médica
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Regla de negocio: Si la cita ya finalizó, no se permite ninguna modificación
            if ($cita['estado'] === 'Finalizada') {
                guardarFlash('error', 'No se puede editar porque esta cita ya finalizó.');
                header('Location: index.php?ruta=medico/atencion&id=' . $idCita);
                exit;
            }

            $diagnostico = trim($_POST['diagnostico'] ?? '');
            $notas = trim($_POST['notas'] ?? '');
            $prescripcionTexto = trim($_POST['prescripcion'] ?? '');
            $indicaciones = trim($_POST['indicaciones'] ?? '');

            if (empty($diagnostico)) {
                guardarFlash('error', 'El diagnóstico médico es obligatorio.');
                header('Location: index.php?ruta=medico/atencion&id=' . $idCita);
                exit;
            }

            // 1. Guardar o actualizar la atención en el historial clínico (marca la cita como Finalizada)
            $this->historialModelo->guardarAtencion($idCita, $diagnostico, $notas);

            // 2. Procesar prescripción de medicamentos si el médico escribió indicaciones libres
            if (!empty($prescripcionTexto)) {
                // Separar por saltos de línea para soportar múltiples medicamentos o indicaciones
                $lineas = preg_split('/\r\n|\r|\n/', $prescripcionTexto);
                $medsValidos = [];

                foreach ($lineas as $linea) {
                    $lineaLimpia = trim($linea);
                    if ($lineaLimpia !== '') {
                        $medsValidos[] = [
                            'medicamento'        => $lineaLimpia,
                            'dosis'              => 'Según indicación',
                            'frecuencia'         => 'Ver posología',
                            'duracion'           => 'Tratamiento',
                            'via_administracion' => 'Oral'
                        ];
                    }
                }

                if (!empty($medsValidos)) {
                    // Verificar si ya existe una receta para esta cita
                    $recetaPrevia = $this->recetaModelo->buscarPorCita($idCita);

                    if ($recetaPrevia) {
                        $this->recetaModelo->actualizarConMedicamentos(
                            (int) $recetaPrevia['id_receta'],
                            $indicaciones,
                            $medsValidos
                        );
                    } else {
                        // Generar código único de receta: REC-AAAAMMDD-####
                        $fecha = date('Ymd');
                        $random = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                        $codigoReceta = "REC-{$fecha}-{$random}";

                        $this->recetaModelo->guardarConMedicamentos(
                            $cita['id_cita'],
                            $cita['id_cliente'],
                            $cita['id_especialista'],
                            $codigoReceta,
                            $indicaciones,
                            $medsValidos
                        );
                    }
                }
            }

            guardarFlash('exito', 'Atención médica y prescripción registradas con éxito. La cita quedó Finalizada.');
            header('Location: index.php?ruta=medico/atencion&id=' . $idCita);
            exit;
        }

        $consultasAnteriores = $this->historialModelo->listarPorCliente($cita['id_cliente']);
        $historialActual = $this->historialModelo->buscarPorCita($idCita);
        $recetaActual = $this->recetaModelo->buscarPorCita($idCita);
        $esFinalizada = ($cita['estado'] === 'Finalizada');

        $titulo = 'Atención Médica - Cita #' . $cita['id_cita'];
        require_once __DIR__ . '/../views/medico/atencion.php';
    }

    // Muestra el formulario para prescribir medicamentos a una cita atendida.
    // Redirige a la sección integrada dentro de la atención médica.
    public function recetaForm(): void {
        requerirRol('medico');
        $idCita = (int)($_GET['id_cita'] ?? 0);
        header('Location: index.php?ruta=medico/atencion&id=' . $idCita . '#seccion-receta');
        exit;
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

        // Si la cita ya finalizó, bloquear edición
        if ($cita['estado'] === 'Finalizada') {
            guardarFlash('error', 'No se puede editar porque esta cita ya finalizó.');
            header('Location: index.php?ruta=medico/atencion&id=' . $idCita);
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

        // Validación de seguridad para paciente: solo puede ver sus propias recetas
        $usuario = usuarioAutenticado();
        if ($usuario && $usuario['rol'] === 'paciente') {
            require_once __DIR__ . '/../models/Cliente.php';
            $clienteModelo = new \Cliente();
            $cliente = $clienteModelo->buscarPorIdUsuario((int) $usuario['id_usuario']);
            if (!$cliente || (int)$receta['id_cliente'] !== (int)$cliente['id_cliente']) {
                guardarFlash('error', 'No tiene permisos para ver esta receta.');
                header('Location: index.php?ruta=paciente/recetas');
                exit;
            }
        }

        $titulo = 'Receta Médica ' . $receta['codigo_receta'];
        require_once __DIR__ . '/../views/medico/receta_imprimible.php';
    }
}

