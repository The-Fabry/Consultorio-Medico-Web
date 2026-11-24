<?php
/**
 * RecepcionController.php
 * Para qué sirve: Controla el escaneo del ticket de llegada y la visualización de la agenda diaria.
 * Trabaja con: models/Cita.php, models/Especialista.php y views/recepcion/.
 * Equivale en NetBeans a: VistaRecepcion.java
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Especialista.php';

class RecepcionController {

    private Cita $citaModelo;
    private Especialista $especialistaModelo;

    public function __construct() {
        requerirRol('recepcion');
        $this->citaModelo = new Cita();
        $this->especialistaModelo = new Especialista();
    }

    // Muestra la pantalla para escanear con la pistola láser o ingresar el código del ticket.
    // Recibe: código opcional por GET. Devuelve: vista control_llegada.php.
    public function controlLlegada(): void {
        $codigo = trim($_GET['codigo'] ?? '');
        $citaEncontrada = null;

        if ($codigo !== '') {
            $citaEncontrada = $this->citaModelo->buscarPorCodigo($codigo);
            if (!$citaEncontrada) {
                guardarFlash('error', "No se encontró ninguna cita con el código: {$codigo}");
            }
        }

        $titulo = 'Control de Llegada de Pacientes';
        require_once __DIR__ . '/../views/recepcion/control_llegada.php';
    }

    // Recibe el código enviado desde el formulario de escaneo y redirige para mostrarlo.
    // Recibe: codigo_barras por POST. Devuelve: redirección.
    public function validarTicket(): void {
        $codigo = trim($_POST['codigo_barras'] ?? '');
        if ($codigo === '') {
            guardarFlash('error', 'Por favor ingrese o escanee un código.');
            header('Location: index.php?ruta=recepcion/control');
            exit;
        }
        header('Location: index.php?ruta=recepcion/control&codigo=' . urlencode($codigo));
        exit;
    }

    // Cambia el estado de la cita a 'En Proceso' cuando el paciente llega al consultorio.
    // Recibe: id_cita por POST. Devuelve: redirección con confirmación.
    public function ingresarCita(): void {
        $idCita = (int)($_POST['id_cita'] ?? 0);
        $retorno = $_POST['retorno'] ?? 'control';
        $cita = $this->citaModelo->buscarPorId($idCita);

        if (!$cita || $cita['estado'] !== 'Pendiente') {
            guardarFlash('error', 'Solo se pueden ingresar citas en estado Pendiente.');
            header('Location: index.php?ruta=recepcion/' . $retorno);
            exit;
        }

        $this->citaModelo->cambiarEstado($idCita, 'En Proceso');
        guardarFlash('exito', "¡Paciente {$cita['nombre_cliente']} ingresado! Ya puede ser atendido por el médico.");

        $destino = ($retorno === 'citas_dia') ? 'recepcion/citas_dia' : 'recepcion/control';
        header('Location: index.php?ruta=' . $destino);
        exit;
    }

    // Muestra la pantalla de agenda del día con pestañas semanales.
    // Recibe: nada. Devuelve: vista citas_dia.php.
    public function citasDia(): void {
        $especialistas = $this->especialistaModelo->listarTodos();
        $titulo = 'Citas del Día';
        require_once __DIR__ . '/../views/recepcion/citas_dia.php';
    }

    // Endpoint AJAX que calcula los 7 días de la semana y devuelve las citas en JSON.
    // Recibe: fecha, id_especialista y estado por GET. Devuelve: JSON.
    public function citasDiaAjax(): void {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $idEspecialista = !empty($_GET['id_especialista']) ? (int)$_GET['id_especialista'] : null;
        $estado = !empty($_GET['estado']) ? trim($_GET['estado']) : null;

        // Calcular la semana de Lunes a Domingo para la fecha recibida
        $ts = strtotime($fecha);
        $diaSemana = (int)date('N', $ts); // 1 = Lunes
        $tsLunes = strtotime('-' . ($diaSemana - 1) . ' days', $ts);

        $diasSemana = [];
        $nombres = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        for ($i = 0; $i < 7; $i++) {
            $tsDia = strtotime("+{$i} days", $tsLunes);
            $fStr = date('Y-m-d', $tsDia);
            $citasDia = $this->citaModelo->listarPorDia($fStr);
            $diasSemana[] = [
                'etiqueta'      => $nombres[$i],
                'dia_numero'    => date('d', $tsDia),
                'fecha'         => $fStr,
                'total_citas'   => count($citasDia),
                'es_seleccionado' => ($fStr === $fecha)
            ];
        }

        $citas = $this->citaModelo->listarPorDia($fecha, $idEspecialista, $estado);

        // Contar citas por estado para el resumen
        $conteos = ['total' => count($citas), 'pendiente' => 0, 'en_proceso' => 0, 'finalizada' => 0];
        foreach ($citas as $c) {
            $st = strtolower(str_replace(' ', '_', $c['estado']));
            if (isset($conteos[$st])) $conteos[$st]++;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'fecha_actual' => $fecha,
            'dias_semana'  => $diasSemana,
            'conteos'      => $conteos,
            'citas'        => $citas
        ]);
        exit;
    }
}

