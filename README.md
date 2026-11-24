# SmartMed Web — Sistema de Consultorio Médico

**SmartMed Web** es una aplicación web sencilla y modular para la gestión integral de un consultorio médico. Permite a los pacientes crear su cuenta y agendar citas médicas con validación de horarios en tiempo real; al personal de recepción registrar la llegada mediante escaneo de código de barras o digitación; y a los médicos especialistas consultar su agenda, revisar el historial previo del paciente, registrar el diagnóstico clínico y emitir recetas médicas electrónicas con código de barras listo para imprimir.

---

## 1. Estructura del Proyecto (Explicación archivo por archivo)

Toda la aplicación se encuentra organizada en la raíz de manera directa, sin capas abstractas ni configuraciones ocultas:

```text
Consultorio Medico Web/
├── index.php                      ← Punto de entrada: lee ?ruta=... y ejecuta el controlador
├── config/
│   ├── conexion.php               ← Conexión a MySQL con PDO a la base consultorio_dbw (ConexionBD.java).
│   └── sesion.php                 ← Controla el inicio de sesión y restringe las pantallas por rol (Sesion.java).
├── controllers/                   ← Reciben las peticiones del usuario, validan y deciden qué mostrar:
│   ├── AuthController.php         ← Gestiona el login, creación de cuentas de pacientes y cierre de sesión.
│   ├── PacienteController.php     ← Controla el agendamiento, visualización de tickets y cancelación de citas.
│   ├── RecepcionController.php    ← Controla la validación del ticket por escáner y la agenda semanal de citas.
│   ├── MedicoController.php       ← Controla la agenda del médico, la consulta clínica y la emisión de recetas.
│   └── ReporteController.php      ← Genera las vistas imprimibles de citas e historial clínico.
├── models/                        ← Clases que contienen las consultas SQL a la base de datos:
│   ├── Usuario.php                ← Consultas de cuentas de usuario y verificación de credenciales.
│   ├── Cliente.php                ← Consultas de información personal de los pacientes.
│   ├── Especialista.php           ← Consultas del listado de médicos especialistas disponibles.
│   ├── Cita.php                   ← Consultas de agendamiento, verificación de horarios y cambio de estados.
│   ├── HistorialClinico.php       ← Consultas y registro de diagnósticos y notas médicas.
│   └── Receta.php                 ← Consultas y guardado de recetas y medicamentos prescritos.
├── views/                         ← Vistas visuales en HTML con PHP mínimo:
│   ├── header.php                 ← Barra superior y menú de navegación según el rol del usuario conectado.
│   ├── footer.php                 ← Pie de página común y cierre de etiquetas.
│   ├── login.php                  ← Formulario de inicio de sesión.
│   ├── registro.php               ← Formulario público para que nuevos pacientes creen su cuenta.
│   ├── paciente/
│   │   ├── agendar.php            ← Formulario con selección de médico, fecha y chips de horas libres.
│   │   ├── mis_citas.php          ← Tabla de citas del paciente con insignias de estado y cancelación.
│   │   ├── ticket.php             ← Comprobante digital con código de barras CODE_128 imprimible.
│   │   └── mis_recetas.php        ← Listado de recetas emitidas al paciente.
│   ├── recepcion/
│   │   ├── control_llegada.php    ← Pantalla con cursor enfocado para escáner láser y botón de ingreso.
│   │   └── citas_dia.php          ← Agenda semanal interactiva con selector AJAX de Lunes a Domingo.
│   ├── medico/
│   │   ├── agenda.php             ← Agenda de citas asignadas al especialista con filtro de fecha y estado.
│   │   ├── atencion.php           ← Ficha clínica con antecedentes del paciente y campo de diagnóstico.
│   │   ├── receta_form.php        ← Formulario dinámico para prescribir medicamentos (+/- filas).
│   │   └── receta_imprimible.php  ← Formato oficial de receta con membrete, firma y código de barras.
│   └── reportes/
│       ├── citas.php              ← Reporte unificado de citas médicas (general o por rango de fechas).
│       └── historial_paciente.php ← Reporte de historial clínico filtrable por cédula de paciente.
├── assets/
│   ├── css/
│   │   └── estilos.css            ← Único archivo de estilos con paleta clínica (#0F4C81) e impresión.
│   ├── js/
│   │   ├── validaciones.js        ← Filtro en tiempo real para impedir letras en cédula y teléfono.
│   │   ├── paciente.js            ← Dibuja el código de barras del ticket y consulta horarios libres por AJAX.
│   │   ├── recepcion.js           ← Mantiene el cursor en el escáner láser y actualiza la semana por AJAX.
│   │   └── medico.js              ← Controla el doble clic de la agenda y filas dinámicas de medicamentos.
│   └── img/
│       └── logo.svg               ← Isotipo del consultorio médico en formato vectorial.
├── database/
│   ├── schema.sql                 ← Estructura de tablas, llaves foráneas e índices en MySQL (consultorio_dbw).
│   ├── triggers.sql               ← Trigger para sincronizar automáticamente usuarios y pacientes.
│   └── seed.sql                   ← Datos iniciales con médicos especialistas y usuarios de prueba.
└── README.md                      ← Guía explicativa para la defensa y exposición del proyecto.
```

---

## 2. Tabla de Equivalencias (NetBeans Swing → Versión Web)

| Aplicación de Escritorio (NetBeans / Java) | Versión Web Simplificada (PHP / JS) | Función Principal |
| :--- | :--- | :--- |
| `ConexionBD.java` | `config/conexion.php` | Conexión con PDO a la base `consultorio_dbw`. |
| `Sesion.java` | `config/sesion.php` | Almacena y valida el usuario activo y su rol. |
| `Login.java` | `controllers/AuthController.php`<br>`views/login.php` | Inicio de sesión con contraseñas hasheadas. |
| *(NUEVA función web)* | `views/registro.php` | Formulario de auto-registro de pacientes. |
| `Login.java (consultas SQL)` | `models/Usuario.php` | Usuario.php — en NetBeans esta consulta estaba dentro de Login.java. |
| `ClienteDAO.java` | `models/Cliente.php` | Consultas SQL de la tabla `clientes`. |
| `EspecialistaDAO.java` | `models/Especialista.php` | Consultas SQL de médicos y especialidades. |
| `CitaDAO.java` | `models/Cita.php` | Consultas de reservas, validación de turnos y estados. |
| `VistaPaciente.java` | `controllers/PacienteController.php`<br>`views/paciente/agendar.php` | Formulario de reserva y consulta de citas. |
| `TicketDigita.java` | `views/paciente/ticket.php` | Ticket con código de barras CODE_128 (JsBarcode). |
| `VistaRecepcion.java` | `controllers/RecepcionController.php`<br>`views/recepcion/control_llegada.php` | Escaneo láser y paso de la cita a *En Proceso*. |
| *(NUEVA función web)* | `views/recepcion/citas_dia.php` | Pestañas de Lunes a Domingo con conteo por AJAX. |
| `VistaMedico.java` | `controllers/MedicoController.php`<br>`views/medico/agenda.php` | Lista de pacientes citados para el especialista. |
| `PanelHistorialMedico.java` | `models/HistorialClinico.php`<br>`views/medico/atencion.php` | Historial previo y registro del diagnóstico. |
| *(NUEVA función web)* | `models/Receta.php`<br>`views/medico/receta_form.php` | Prescripción dinámica de fármacos y dosis. |
| `GestorReportes.java` | `controllers/ReporteController.php`<br>`views/reportes/citas.php` | Vistas web nativas optimizadas para imprimir. |
| Estilos / Look & Feel | `assets/css/estilos.css` | Diseño médico con variables en `:root`. |

---

## 3. Cómo Funciona una Petición en 5 Pasos

1. **El usuario hace clic o envía un formulario:** El navegador envía la petición al servidor (ejemplo: `index.php?ruta=paciente/agendar`).
2. **`index.php` recibe la ruta:** Inicia la sesión, lee el parámetro `$_GET['ruta']` y mediante un `switch` ejecuta el controlador y método correspondiente.
3. **El Controlador procesa la lógica:** Verifica los permisos del rol mediante `requerirRol()`, recibe y valida los datos recibidos por POST o GET.
4. **El Modelo ejecuta el SQL:** El controlador llama a las funciones del modelo (ejemplo: `Cita::guardarCita`), el cual ejecuta consultas preparadas con PDO (`?`) protegiendo la base de datos contra inyecciones SQL.
5. **La Vista muestra el resultado:** El controlador incluye la vista correspondiente (ejemplo: `views/paciente/agendar.php`), la cual se renderiza entre `views/header.php` y `views/footer.php` mostrando la información al usuario.

### Mapa de Rutas de `index.php` (Todas verificadas)
- `login` → `AuthController::login`
- `registro` → `AuthController::registro`
- `logout` → `AuthController::logout`
- `paciente/agendar` → `PacienteController::agendar`
- `paciente/citas` → `PacienteController::misCitas`
- `paciente/ticket` → `PacienteController::verTicket`
- `paciente/cancelar` → `PacienteController::cancelar`
- `paciente/recetas` → `PacienteController::misRecetas`
- `api/horarios-ocupados` → `PacienteController::horariosOcupados` (AJAX)
- `recepcion/control` → `RecepcionController::controlLlegada`
- `recepcion/validar` → `RecepcionController::validarTicket`
- `recepcion/ingresar` → `RecepcionController::ingresarCita`
- `recepcion/citas_dia` → `RecepcionController::citasDia`
- `api/citas-dia` → `RecepcionController::citasDiaAjax` (AJAX)
- `medico/agenda` → `MedicoController::agenda`
- `medico/atencion` → `MedicoController::atencion`
- `medico/receta` → `MedicoController::recetaForm`
- `medico/receta_guardar` → `MedicoController::guardarReceta`
- `medico/receta_imprimible` → `MedicoController::verReceta`
- `reportes/citas` → `ReporteController::citas`
- `reportes/historial` → `ReporteController::historial`

---

## 4. Flujo de Estados de una Cita Médica y Generación de Recetas

```text
[ PACIENTE AGENDAR ] 
        │ 
        ▼
   ( Pendiente ) ──(Paciente cancela cita)──► [ Cancelada ] (Libera horario)
        │
(Recepción escanea código de barras o valida a mano)
        │
        ▼
  ( En Proceso ) ──(Médico atiende y guarda diagnóstico)──► [ Finalizada ] (Cita atendida)
        │                                                         │
        └───────► [ SE PUEDE GENERAR RECETA MÉDICA ] ◄────────────┘
```

- **`Pendiente`:** La cita fue creada por el paciente. Puede ser cancelada libremente por el paciente antes de la fecha. **No se puede emitir receta**.
- **`En Proceso`:** El paciente llegó al consultorio y recepción confirmó su asistencia. El médico ya puede atenderlo. **Aquí ya se puede emitir la receta médica** durante la consulta.
- **`Finalizada`:** El médico completó la atención y registró el diagnóstico obligatorio en una transacción atómica. La cita queda cerrada. **También se puede emitir o reimprimir la receta médica** si es necesario.
- **`Cancelada`:** Borrado lógico (`cancelada_en = NOW()`) que libera el horario del médico para que otro paciente pueda reservarlo. **No se puede emitir receta**.

> **Resumen de la regla:** La receta médica se puede generar tanto cuando la cita está **`En Proceso`** (durante la atención de la consulta médica) como cuando ya ha sido **`Finalizada`** (no se puede emitir en citas `Pendiente` ni `Cancelada`).

---

## 5. Cómo Crear un Médico o Recepcionista

En SmartMed Web, el formulario público de registro está destinado exclusivamente para que los **pacientes** creen su propia cuenta (con rol fijo `paciente`).

Los **médicos** y el personal de **recepción** no se auto-registran desde la web; se crean directamente en el archivo `database/seed.sql`:

1. Abra `database/seed.sql` con cualquier editor de texto.
2. Para un **recepcionista**, agregue una fila en el `INSERT INTO usuarios`:
   ```sql
   INSERT INTO usuarios (usuario, password, rol, cedula, nombre, telefono, activo) VALUES
   ('recepcion2', '$2y$10$W29.UQJok5GGSqvb/17WTesuD8OvPvYowFJBlR1w0QrWn6jwACexu', 'recepcion', '1700000099', 'Recepcionista Tarde', '0990000099', 1);
   ```
3. Para un **médico especialista**, se registran dos cosas con la **misma cédula**:
   - En la tabla `especialistas`:
     ```sql
     INSERT INTO especialistas (nombre_completo, especialidad, cedula) VALUES
     ('Dr. Manuel Soria', 'Dermatología', '1755667788');
     ```
   - En la tabla `usuarios` (con rol `'medico'`):
     ```sql
     INSERT INTO usuarios (usuario, password, rol, cedula, nombre, telefono, activo) VALUES
     ('dr_soria', '$2y$10$WueNFitTFZxrywRYG8mgouim17T4Ml1FXsr14LBDll0BIUnJ1d2ni', 'medico', '1755667788', 'Dr. Manuel Soria', '0990000088', 1);
     ```
4. Las contraseñas se guardan hasheadas con BCRYPT mediante la función nativa de PHP `password_hash('tu_clave', PASSWORD_BCRYPT)`.

---

## 6. Instalación en XAMPP y Usuarios de Prueba

### Instalación Rápida
1. Copie la carpeta `Consultorio Medico Web` dentro de `C:\xampp\htdocs\`.
2. En el panel de XAMPP inicie **Apache** y **MySQL**.
3. En **phpMyAdmin** (`http://localhost/phpmyadmin/`), importe en la pestaña **Importar** los tres archivos en este orden (el script `schema.sql` crea automáticamente la base de datos `consultorio_dbw` limpia):
   1. `database/schema.sql` (crea la base `consultorio_dbw`, tablas e índices con cédula UNIQUE).
   2. `database/triggers.sql` (crea el trigger que sincroniza usuarios sin duplicar médicos ni alterar su especialidad).
   3. `database/seed.sql` (inserta los 4 médicos especialistas, usuarios y citas demo).
4. Abra su navegador e ingrese a:
   ```text
   http://localhost/Consultorio%20Medico%20Web/index.php
   ```

### Usuarios Demo para la Exposición (Un médico por cada especialista)

| Rol | Usuario | Contraseña | Médico / Especialidad | Cédula | Pantalla Inicial |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Recepción** | `recepcion` | `recepcion123` | Recepción Principal | `1700000002` | `index.php?ruta=recepcion/control` |
| **Médico 1** | `dr_gomez` | `medico123` | Dr. Roberto Gómez &bull; *Cardiología* | `1711223344` | `index.php?ruta=medico/agenda` |
| **Médico 2** | `dra_ortiz` | `medico123` | Dra. Carmen Ortiz &bull; *Medicina General* | `1722334455` | `index.php?ruta=medico/agenda` |
| **Médico 3** | `dra_paredes` | `medico123` | Dra. Valeria Paredes &bull; *Pediatría* | `1733445566` | `index.php?ruta=medico/agenda` |
| **Médico 4** | `dr_andrade` | `medico123` | Dr. Esteban Andrade &bull; *Traumatología* | `1744556677` | `index.php?ruta=medico/agenda` |
| **Paciente** | `juan_perez` | `paciente123` | Juan Pérez (Paciente) | `1799887766` | `index.php?ruta=paciente/agendar` |
