<?php
/**
 * index.php
 * Para qué sirve: Punto de entrada único del sistema. Lee la ruta solicitada y llama al controlador.
 * Trabaja con: config/conexion.php, config/sesion.php y la carpeta controllers/.
 * Equivale en NetBeans a: Clase Principal.java o método main() que abría el JFrame inicial.
 */

// Se cargan la conexión a la base de datos y la sesión
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/sesion.php';

// Inicia la sesión de usuario
iniciarSesion();

// Se lee la ruta por la URL (ejemplo: index.php?ruta=login). Si no viene ninguna, por defecto es 'login'
$ruta = $_GET['ruta'] ?? 'login';

// Enrutador sencillo con switch: relaciona cada ruta con su controlador y método correspondiente
switch ($ruta) {

    // === RUTAS DE AUTENTICACIÓN (LOGIN Y REGISTRO) ===
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'registro':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->registro();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    // === RUTAS DEL PACIENTE ===
    case 'paciente/agendar':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->agendar();
        break;

    case 'paciente/citas':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->misCitas();
        break;

    case 'paciente/ticket':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->verTicket();
        break;

    case 'paciente/cancelar':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->cancelar();
        break;

    case 'paciente/recetas':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->misRecetas();
        break;

    // Petición AJAX para saber qué horas ya están reservadas por un médico
    case 'api/horarios-ocupados':
        require_once __DIR__ . '/controllers/PacienteController.php';
        (new PacienteController())->horariosOcupados();
        break;

    // === RUTAS DE RECEPCIÓN ===
    case 'recepcion/control':
        require_once __DIR__ . '/controllers/RecepcionController.php';
        (new RecepcionController())->controlLlegada();
        break;

    case 'recepcion/validar':
        require_once __DIR__ . '/controllers/RecepcionController.php';
        (new RecepcionController())->validarTicket();
        break;

    case 'recepcion/ingresar':
        require_once __DIR__ . '/controllers/RecepcionController.php';
        (new RecepcionController())->ingresarCita();
        break;

    case 'recepcion/citas_dia':
        require_once __DIR__ . '/controllers/RecepcionController.php';
        (new RecepcionController())->citasDia();
        break;

    // Petición AJAX para actualizar la tabla de la semana sin recargar la página
    case 'api/citas-dia':
        require_once __DIR__ . '/controllers/RecepcionController.php';
        (new RecepcionController())->citasDiaAjax();
        break;

    // === RUTAS DEL MÉDICO ===
    case 'medico/agenda':
        require_once __DIR__ . '/controllers/MedicoController.php';
        (new MedicoController())->agenda();
        break;

    case 'medico/atencion':
        require_once __DIR__ . '/controllers/MedicoController.php';
        (new MedicoController())->atencion();
        break;

    case 'medico/receta':
        require_once __DIR__ . '/controllers/MedicoController.php';
        (new MedicoController())->recetaForm();
        break;

    case 'medico/receta_guardar':
        require_once __DIR__ . '/controllers/MedicoController.php';
        (new MedicoController())->guardarReceta();
        break;

    case 'medico/receta_imprimible':
        require_once __DIR__ . '/controllers/MedicoController.php';
        (new MedicoController())->verReceta();
        break;

    // === RUTAS DE REPORTES ===
    case 'reportes/citas':
        require_once __DIR__ . '/controllers/ReporteController.php';
        (new ReporteController())->citas();
        break;

    case 'reportes/historial':
        require_once __DIR__ . '/controllers/ReporteController.php';
        (new ReporteController())->historial();
        break;

    case 'reportes/pdf':
        require_once __DIR__ . '/views/reportes/reporte_dompdf.php';
        break;

    // Si la ruta escrita en la URL no existe en la lista
    default:
        http_response_code(404);
        echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
        echo "<h2>Página no encontrada</h2>";
        echo "<p>La pantalla solicitada no existe.</p>";
        echo "<a href='index.php?ruta=login'>Volver al inicio</a>";
        echo "</div>";
        break;
}

