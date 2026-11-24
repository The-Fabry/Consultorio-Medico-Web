<?php
/**
 * reporte_dompdf.php
 * Para qué sirve: Generador de documentos PDF oficiales con la librería Dompdf, consultando
 *                 directamente la base de datos según el tipo de reporte y los permisos del rol en sesión.
 * Trabaja con: config/sesion.php, config/conexion.php, views/reportes/dompdf/autoload.inc.php y modelos.
 * 
 * Flujo paso a paso para la defensa del proyecto:
 * 1. Control de acceso por sesión: Valida que el usuario tenga rol autorizado ('medico', 'recepcion' o 'admin').
 * 2. Carga de Dompdf: Incluye dompdf/autoload.inc.php y configura las opciones (fuentes Unicode, soporte de imágenes).
 * 3. Consulta a la base de datos: Según el parámetro 'tipo' ('citas', 'historial', 'receta'), consulta con PDO.
 *    - Si es médico, filtra únicamente sus citas asignadas; si es recepción/admin, muestra el reporte global.
 * 4. Construcción del HTML con buffer (ob_start / ob_get_clean): Renderiza el HTML con la cabecera institucional
 *    azul marino y tablas compatibles con Dompdf.
 * 5. Generación y streaming del PDF: loadHtml -> setPaper('A4', 'portrait') -> render -> stream().
 */

require_once __DIR__ . '/../../config/sesion.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Control de acceso: Médicos, recepcionistas y administradores pueden exportar reportes
requerirRol(['medico', 'recepcion', 'admin']);

$usuarioSesion = usuarioActual();
$rolSesion = $usuarioSesion['rol'] ?? '';
$pdo = conectarBD();

// Obtener parámetros de la petición GET
$tipoReporte = trim($_GET['tipo'] ?? 'citas');
$fechaEmision = date('d/m/Y H:i');

// Variables que se llenarán según el tipo de reporte
$tituloReporte = "SMARTMED - REPORTE OFICIAL";
$subtituloReporte = "Documento clínico generado desde el sistema";
$contextoReporte = "";
$filasHtml = "";
$nombreArchivoPdf = "Reporte_SmartMed_" . date('Ymd_His') . ".pdf";

// Si el usuario es médico, obtenemos su ficha de especialista para restringir datos
$especialistaSesion = null;
if ($rolSesion === 'medico') {
    $stmtEsp = $pdo->prepare("SELECT * FROM especialistas WHERE cedula = ? LIMIT 1");
    $stmtEsp->execute([$usuarioSesion['cedula'] ?? '']);
    $especialistaSesion = $stmtEsp->fetch();
}

// 2. Consulta y preparación de datos según el tipo de reporte
switch ($tipoReporte) {

    // === CASO 1: REPORTE DE CITAS MÉDICAS ===
    case 'citas':
        $tituloReporte = "SMARTMED - REPORTE DE CITAS";
        $subtituloReporte = "Control y auditoría de citas médicas, especialidades y estados de atención";
        $nombreArchivoPdf = "Reporte_Citas_" . date('Ymd_His') . ".pdf";

        $fechaInicio = trim($_GET['fecha_inicio'] ?? '');
        $fechaFin    = trim($_GET['fecha_fin'] ?? '');

        $sql = "SELECT c.id_cita, c.fecha_cita, c.codigo_barras, c.estado,
                       cl.nombre AS paciente, cl.cedula, e.nombre_completo AS especialista, e.especialidad
                FROM citas c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON c.id_especialista = e.id_especialista";
        $condiciones = [];
        $params = [];

        // Filtro estricto por médico en sesión
        if ($rolSesion === 'medico') {
            $idEspecialista = $especialistaSesion ? (int)$especialistaSesion['id_especialista'] : 0;
            $condiciones[] = "c.id_especialista = ?";
            $params[] = $idEspecialista;
            $contextoReporte = "Médico: " . ($especialistaSesion ? $especialistaSesion['nombre_completo'] : 'No asignado');
        } else {
            $contextoReporte = "Alcance: Vista General del Consultorio (Recepción)";
        }

        // Filtro opcional por rango de fechas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $condiciones[] = "DATE(c.fecha_cita) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
            $contextoReporte .= " &bull; Período: " . date('d/m/Y', strtotime($fechaInicio)) . " al " . date('d/m/Y', strtotime($fechaFin));
        } else {
            $contextoReporte .= " &bull; Histórico Completo";
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        $sql .= " ORDER BY c.fecha_cita DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $citas = $stmt->fetchAll();
        $contextoReporte .= " &bull; Total Citas: " . count($citas);

        // Construir tabla de citas
        ob_start();
        ?>
        <table class="tabla-pdf">
            <thead>
                <tr>
                    <th style="width: 10%;">N.º Cita</th>
                    <th style="width: 16%;">Fecha y Hora</th>
                    <th style="width: 16%;">Ticket</th>
                    <th style="width: 22%;">Paciente</th>
                    <th style="width: 22%;">Médico Especialista</th>
                    <th style="width: 14%; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748B; padding: 20px;">No se encontraron citas con los criterios seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $c): ?>
                        <tr>
                            <td><strong>#<?= str_pad((string)$c['id_cita'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_cita'])) ?></td>
                            <td><span class="codigo-ticket"><?= htmlspecialchars($c['codigo_barras'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($c['paciente'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <span style="font-size: 8pt; color: #64748B;">C.I. <?= htmlspecialchars($c['cedula'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($c['especialista'], ENT_QUOTES, 'UTF-8') ?><br>
                                <span style="font-size: 8pt; color: #0284C7;"><?= htmlspecialchars($c['especialidad'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-pdf badge-<?= strtolower(str_replace(' ', '-', $c['estado'])) ?>">
                                    <?= htmlspecialchars($c['estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $filasHtml = ob_get_clean();
        break;

    // === CASO 2: HISTORIAL CLÍNICO DE PACIENTES ===
    case 'historial':
        $tituloReporte = "SMARTMED - HISTORIAL CLÍNICO";
        $subtituloReporte = "Expediente médico de consultas, diagnósticos clínicos y notas de evolución";
        $nombreArchivoPdf = "Historial_Clinico_" . date('Ymd_His') . ".pdf";

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
            $sql .= " WHERE cl.cedula = ?";
            $params = [$cedula];
            $stmtCl = $pdo->prepare("SELECT * FROM clientes WHERE cedula = ? LIMIT 1");
            $stmtCl->execute([$cedula]);
            $cliente = $stmtCl->fetch();
        }

        $sql .= " ORDER BY c.fecha_cita DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $historiales = $stmt->fetchAll();

        if ($cliente) {
            $contextoReporte = "Paciente: <strong>" . htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') . "</strong> (C.I. " . htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8') . " &bull; Tel: " . htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8') . ") &bull; Total Consultas: " . count($historiales);
        } else {
            $contextoReporte = "Historial General del Consultorio &bull; Total Atenciones Registradas: " . count($historiales);
        }

        // Construir fichas de historial
        ob_start();
        ?>
        <?php if (empty($historiales)): ?>
            <div style="text-align: center; color: #64748B; padding: 30px; border: 1px solid #CBD5E1; border-radius: 6px;">
                No se registraron atenciones clínicas para el criterio ingresado.
            </div>
        <?php else: ?>
            <?php foreach ($historiales as $h): ?>
                <div class="ficha-historial-pdf">
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
                        <tr>
                            <td style="width: 60%; text-align: left;">
                                <strong style="color: #0F4C81; font-size: 10pt;"><?= htmlspecialchars($h['paciente'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span style="font-size: 8pt; color: #64748B; margin-left: 6px;">C.I. <?= htmlspecialchars($h['cedula'], ENT_QUOTES, 'UTF-8') ?> &bull; Tel: <?= htmlspecialchars($h['telefono'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td style="width: 40%; text-align: right; font-size: 8.5pt; color: #64748B;">
                                Fecha Consulta: <strong><?= date('d/m/Y H:i', strtotime($h['fecha_cita'])) ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 8.5pt; color: #0284C7; padding-top: 2px;">
                                Médico Tratante: <strong><?= htmlspecialchars($h['especialista'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($h['especialidad'], ENT_QUOTES, 'UTF-8') ?>)
                            </td>
                        </tr>
                    </table>

                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 8px 10px; border-radius: 4px; margin-top: 4px;">
                        <div style="font-weight: bold; color: #0F172A; font-size: 9pt;">
                            Diagnóstico: <span style="font-weight: normal;"><?= htmlspecialchars($h['diagnostico'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php if (!empty($h['notas'])): ?>
                            <div style="font-size: 8.5pt; color: #475569; margin-top: 4px;">
                                <em>Notas y Evolución: <?= htmlspecialchars($h['notas'], ENT_QUOTES, 'UTF-8') ?></em>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php
        $filasHtml = ob_get_clean();
        break;

    // === CASO 3: RECETA MÉDICA (OPCIONAL O POR ID) ===
    case 'receta':
        $idReceta = (int)($_GET['id'] ?? 0);
        $tituloReporte = "SMARTMED - RECETAS MÉDICAS";
        $subtituloReporte = "Prescripción farmacológica oficial, dosificación y recomendaciones terapéuticas";
        $nombreArchivoPdf = "Receta_Medica_{$idReceta}.pdf";

        $sql = "SELECT r.*, cl.nombre AS nombre_cliente, cl.cedula AS cedula_cliente,
                       e.nombre_completo AS nombre_especialista, e.especialidad, c.fecha_cita
                FROM recetas r
                INNER JOIN clientes cl ON r.id_cliente = cl.id_cliente
                INNER JOIN especialistas e ON r.id_especialista = e.id_especialista
                INNER JOIN citas c ON r.id_cita = c.id_cita
                WHERE r.id_receta = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idReceta]);
        $receta = $stmt->fetch();

        if ($receta) {
            $contextoReporte = "Paciente: " . htmlspecialchars($receta['nombre_cliente'], ENT_QUOTES, 'UTF-8') . " (C.I. " . htmlspecialchars($receta['cedula_cliente'], ENT_QUOTES, 'UTF-8') . ") &bull; Médico: " . htmlspecialchars($receta['nombre_especialista'], ENT_QUOTES, 'UTF-8');
            $stmtMeds = $pdo->prepare("SELECT * FROM receta_medicamentos WHERE id_receta = ?");
            $stmtMeds->execute([$idReceta]);
            $medicamentos = $stmtMeds->fetchAll();

            ob_start();
            ?>
            <table class="tabla-pdf">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosis</th>
                        <th>Frecuencia</th>
                        <th>Duración</th>
                        <th>Vía</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicamentos as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['medicamento'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($m['dosis'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($m['frecuencia'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($m['duracion'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($m['via_administracion'] ?? 'Oral', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!empty($receta['indicaciones'])): ?>
                <div style="margin-top: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px; border-radius: 4px;">
                    <strong>Indicaciones Generales:</strong><br>
                    <?= nl2br(htmlspecialchars($receta['indicaciones'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
            <?php endif; ?>
            <?php
            $filasHtml = ob_get_clean();
        } else {
            $contextoReporte = "Receta no encontrada";
            $filasHtml = "<p style='text-align:center; color:red;'>No se encontró la receta solicitada.</p>";
        }
        break;

    default:
        die("Tipo de reporte no válido.");
}

// 3. Plantilla HTML completa compatible al 100% con el motor de renderizado de Dompdf
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tituloReporte, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
            size: a4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #0F172A;
            line-height: 1.4;
            background: #FFFFFF;
        }

        /* Cabecera superior unificada azul marino compatible con Dompdf (usando tabla) */
        .cabecera-pdf {
            width: 100%;
            border-collapse: collapse;
            background-color: #0F355C;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .cabecera-pdf td {
            padding: 14px 18px;
            vertical-align: middle;
            color: #FFFFFF;
        }

        .cabecera-marca-tag {
            font-size: 7pt;
            font-weight: bold;
            color: #BAE6FD;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .cabecera-titulo {
            font-size: 13pt;
            font-weight: bold;
            color: #FFFFFF;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }

        .cabecera-subtitulo {
            font-size: 8pt;
            color: #E0F2FE;
            margin: 0;
        }

        .cabecera-contexto {
            background-color: #1E40AF;
            border-left: 3px solid #38BDF8;
            padding: 4px 8px;
            font-size: 7.5pt;
            color: #F0F9FF;
            margin-top: 6px;
            border-radius: 2px;
        }

        .cabecera-meta-caja {
            background-color: #1E40AF;
            border: 1px solid #38BDF8;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: right;
            font-size: 7.5pt;
        }

        /* Tablas de datos limpias y estilizadas */
        .tabla-pdf {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8.5pt;
        }

        .tabla-pdf th {
            background-color: #F1F5F9;
            color: #0F4C81;
            font-weight: bold;
            border: 1px solid #CBD5E1;
            padding: 8px 6px;
            text-align: left;
        }

        .tabla-pdf td {
            border: 1px solid #E2E8F0;
            padding: 7px 6px;
            vertical-align: middle;
        }

        .tabla-pdf tr:nth-child(even) td {
            background-color: #FAFCFE;
        }

        .codigo-ticket {
            font-family: monospace;
            background-color: #F1F5F9;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8pt;
            color: #0F172A;
            border: 1px solid #E2E8F0;
        }

        /* Badges de estado en PDF */
        .badge-pdf {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pendiente { background-color: #FEF3C7; color: #92400E; }
        .badge-en-proceso { background-color: #E0F2FE; color: #0369A1; }
        .badge-finalizada { background-color: #DCFCE7; color: #15803D; }
        .badge-cancelada { background-color: #FEE2E2; color: #991B1B; }

        /* Ficha de historial clínico */
        .ficha-historial-pdf {
            border: 1px solid #CBD5E1;
            border-left: 4px solid #0F4C81;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        /* Pie de página oficial */
        .pie-pdf {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #64748B;
            border-top: 1px solid #E2E8F0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Cabecera Superior Azul Marino Estandarizada -->
    <table class="cabecera-pdf">
        <tr>
            <td style="width: 65%;">
                <div class="cabecera-marca-tag">✚ SMARTMED CONSULTORIO &bull; DOCUMENTO OFICIAL</div>
                <div class="cabecera-titulo"><?= htmlspecialchars($tituloReporte, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="cabecera-subtitulo"><?= htmlspecialchars($subtituloReporte, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($contextoReporte)): ?>
                    <div class="cabecera-contexto"><?= $contextoReporte ?></div>
                <?php endif; ?>
            </td>
            <td style="width: 35%; text-align: right;">
                <div class="cabecera-meta-caja">
                    <span style="color: #BAE6FD; font-weight: bold;">EMISIÓN:</span><br>
                    <strong><?= $fechaEmision ?></strong><br>
                    <span style="font-size: 6.5pt; color: #E0F2FE;">Certificación Digital SmartMed Web</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Contenido dinámico del reporte -->
    <main>
        <?= $filasHtml ?>
    </main>

    <!-- Pie de página con numeración y garantía -->
    <div class="pie-pdf">
        SmartMed Web &bull; Sistema Integral de Gestión Médica &bull; Impreso el <?= $fechaEmision ?>
    </div>

</body>
</html>
<?php
$htmlGenerado = ob_get_clean();

// 4. Configurar e instanciar Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($htmlGenerado);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 5. Enviar el PDF directamente al navegador sin forzar descarga inmediata (Attachment: false)
$dompdf->stream($nombreArchivoPdf, ["Attachment" => false]);
exit;
