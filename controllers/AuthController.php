<?php
/**
 * AuthController.php
 * Para qué sirve: Controla el inicio de sesión, creación de cuentas y cierre de sesión.
 * Trabaja con: models/Usuario.php y las vistas views/login.php y views/registro.php.
 * Equivale en NetBeans a: Login.java
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    private Usuario $usuarioModelo;

    public function __construct() {
        $this->usuarioModelo = new Usuario();
    }

    // Muestra y procesa el inicio de sesión.
    // Recibe: usuario y password por POST. Devuelve: vista o redirección.
    public function login(): void {
        // Si el usuario ya está conectado, se redirige según su rol
        $usuario = usuarioActual();
        if ($usuario) {
            $this->redirigirPorRol($usuario['rol']);
            return;
        }

        // Si se envió el formulario por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreUsuario = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($nombreUsuario) || empty($password)) {
                guardarFlash('error', 'Complete todos los campos para ingresar.');
                header('Location: index.php?ruta=login');
                exit;
            }

            $datos = $this->usuarioModelo->buscarPorUsuario($nombreUsuario);

            // Validar existencia y verificar hash de la contraseña
            if (!$datos || !password_verify($password, $datos['password'])) {
                guardarFlash('error', 'Usuario o contraseña incorrectos.');
                header('Location: index.php?ruta=login');
                exit;
            }

            if ((int)$datos['activo'] !== 1) {
                guardarFlash('error', 'Su cuenta se encuentra inactiva. Contacte a recepción.');
                header('Location: index.php?ruta=login');
                exit;
            }

            // Iniciar sesión y guardar datos en $_SESSION
            $_SESSION['usuario_id'] = $datos['id_usuario'];
            $_SESSION['usuario_nombre_usuario'] = $datos['usuario'];
            $_SESSION['usuario_nombre'] = $datos['nombre'];
            $_SESSION['usuario_rol'] = $datos['rol'];
            $_SESSION['usuario_cedula'] = $datos['cedula'];
            $_SESSION['usuario_telefono'] = $datos['telefono'];

            guardarFlash('exito', '¡Bienvenido(a), ' . $datos['nombre'] . '!');
            $this->redirigirPorRol($datos['rol']);
            return;
        }

        // Si es GET, muestra la pantalla de login
        $titulo = 'Iniciar Sesión';
        require_once __DIR__ . '/../views/login.php';
    }

    // Muestra y procesa el registro público de nuevos pacientes.
    // Recibe: datos del paciente por POST. Devuelve: vista o redirección a login.
    public function registro(): void {
        $usuario = usuarioActual();
        if ($usuario) {
            $this->redirigirPorRol($usuario['rol']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $cedula = trim($_POST['cedula'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $nombreUsuario = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmar = $_POST['confirmar_password'] ?? '';

            // Validaciones básicas de campos
            if (empty($nombre) || empty($cedula) || empty($telefono) || empty($nombreUsuario) || empty($password)) {
                guardarFlash('error', 'Complete todos los campos requeridos.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            // Validación de cédula (10 dígitos numéricos)
            if (strlen($cedula) !== 10 || !ctype_digit($cedula)) {
                guardarFlash('error', 'La cédula debe tener exactamente 10 dígitos numéricos.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            // Validación de teléfono (10 dígitos numéricos)
            if (strlen($telefono) !== 10 || !ctype_digit($telefono)) {
                guardarFlash('error', 'El teléfono debe tener exactamente 10 dígitos numéricos.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            if ($password !== $confirmar) {
                guardarFlash('error', 'Las contraseñas no coinciden.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            if (strlen($password) < 6) {
                guardarFlash('error', 'La contraseña debe tener al menos 6 caracteres.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            if ($this->usuarioModelo->existeUsuario($nombreUsuario)) {
                guardarFlash('error', 'El nombre de usuario ya está ocupado.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            if ($this->usuarioModelo->existeCedula($cedula)) {
                guardarFlash('error', 'La cédula ingresada ya tiene una cuenta registrada.');
                header('Location: index.php?ruta=registro');
                exit;
            }

            // Guardar usuario paciente con contraseña hasheada
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $this->usuarioModelo->registrarPaciente([
                'usuario'  => $nombreUsuario,
                'password' => $hash,
                'cedula'   => $cedula,
                'nombre'   => $nombre,
                'telefono' => $telefono
            ]);

            guardarFlash('exito', 'Cuenta creada con éxito. Ya puede iniciar sesión.');
            header('Location: index.php?ruta=login');
            exit;
        }

        $titulo = 'Crear Cuenta de Paciente';
        require_once __DIR__ . '/../views/registro.php';
    }

    // Cierra la sesión activa y manda al usuario a la pantalla de login.
    // Recibe: nada. Devuelve: redirección.
    public function logout(): void {
        iniciarSesion();
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?ruta=login');
        exit;
    }

    // Redirige al usuario a la pantalla principal según su rol.
    // Recibe: rol del usuario. Devuelve: void.
    private function redirigirPorRol(string $rol): void {
        switch ($rol) {
            case 'paciente':
                header('Location: index.php?ruta=paciente/agendar');
                break;
            case 'medico':
                header('Location: index.php?ruta=medico/agenda');
                break;
            case 'recepcion':
                header('Location: index.php?ruta=recepcion/control');
                break;
            default:
                header('Location: index.php?ruta=login');
                break;
        }
        exit;
    }
}

