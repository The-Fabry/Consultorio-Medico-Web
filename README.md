# SmartMed Web — Sistema de Gestión para Consultorio Médico

**SmartMed Web** es una solución web integral, modular y de alto rendimiento diseñada bajo la arquitectura **Modelo-Vista-Controlador (MVC)** en **PHP 8 nativo**, **MySQL/PDO** y **JavaScript Vanilla**. El sistema automatiza y sincroniza todo el ciclo de atención clínica de un consultorio: desde el auto-registro de pacientes y la reserva de citas en franjas horarias validadas en tiempo real, hasta la recepción asistida por código de barras, la consulta médica especializada con diagnóstico y prescripción integrada, y la generación oficial de reportes y recetas en formato **PDF** mediante la librería **Dompdf**.

---

## 1. Tecnologías Utilizadas

* **Backend:** PHP 8.x con arquitectura MVC limpia, Front Controller (`index.php`), sesiones nativas seguras y tipado estricto sin dependencias de frameworks externos pesados.
* **Base de Datos:** MySQL / MariaDB con motor transaccional **InnoDB**, juego de caracteres `utf8mb4_unicode_ci`, llaves foráneas (`CASCADE`/`RESTRICT`), triggers automáticos de sincronización y abstracción mediante **PDO** con sentencias preparadas contra ataques de inyección SQL.
* **Frontend:** HTML5 semántico y CSS3 moderno organizado con variables globales `:root`, diseño responsivo, pantalla de autenticación dividida (*Split-Screen Login*) con identidad corporativa SmartMed, componentes de alerta accesibles y reglas `@media print` con calibración cromática exacta (`print-color-adjust: exact`).
* **JavaScript:** JavaScript nativo (ES6+) sin frameworks, con peticiones asíncronas **AJAX/Fetch**, validación de entradas numéricas en tiempo real (cédula y teléfono) e interactividad para citas dinámicas.
* **Librerías Integradas:**
  * **JsBarcode (v3.11.5):** Generación vectorial offline de códigos de barra estándar **CODE_128** en tickets y recetas.
  * **Dompdf:** Generador de documentos PDF para la exportación de reportes institucionales y expedientes clínicos desde la base de datos.

---

## 2. Estructura Real de Carpetas y Archivos

El proyecto mantiene una estructura transparente y auto-contenida, facilitando la comprensión y defensa ante un tribunal evaluador:

```text
Consultorio Medico Web/
├── index.php                      # Enrutador frontal (Front Controller): recibe ?ruta=... y despacha al controlador
├── config/                        # Configuraciones centrales del sistema
│   ├── conexion.php               # Conexión singleton a MySQL mediante PDO (base: consultorio_dbw)
│   └── sesion.php                 # Manejador de sesiones, control de acceso por rol y helpers flash
├── controllers/                   # Controladores (Capa de Negocio y Coordinación)
│   ├── AuthController.php         # Autenticación, control de accesos, registro de pacientes y logout
│   ├── PacienteController.php     # Gestión de citas, consulta de horarios ocupados, tickets y recetas
│   ├── RecepcionController.php    # Control de llegada con escáner láser, validación de turnos y agenda semanal
│   ├── MedicoController.php       # Agenda del especialista, atención clínica, diagnóstico y prescripción
│   └── ReporteController.php      # Control de reportes de citas e historiales médicos
├── models/                        # Modelos (Capa de Datos y Consultas SQL con PDO)
│   ├── Usuario.php                # Gestión de cuentas de usuario y verificación de credenciales con BCRYPT
│   ├── Cliente.php                # Información personal de pacientes vinculados por cédula
│   ├── Especialista.php           # Catálogo de médicos especialistas y especialidades
│   ├── Cita.php                   # Agendamiento, verificación de solapamiento de horarios y cambios de estado
│   ├── HistorialClinico.php       # Registro y consulta de diagnósticos clínicos y notas de evolución
│   └── Receta.php                 # Registro de prescripciones farmacológicas y detalle de medicamentos
├── views/                         # Vistas (Capa de Presentación HTML5 / PHP)
│   ├── header.php                 # Cabecera HTML, topbar de navegación adaptativa por rol y mensajes flash
│   ├── footer.php                 # Pie de página institucional y cierre de estructura
│   ├── login.php                  # Pantalla de inicio de sesión dividida (Split-Screen) con logo SmartMed
│   ├── registro.php               # Formulario público de auto-registro para pacientes
│   ├── paciente/                  # Vistas específicas del portal del paciente
│   │   ├── agendar.php            # Selección de médico, selector de fecha y franjas horarias interactivas
│   │   ├── mis_citas.php          # Tabla de citas agendadas con insignias de estado y cancelación
│   │   ├── mis_recetas.php        # Listado de recetas emitidas al paciente logueado
│   │   └── ticket.php             # Comprobante digital con código de barras CODE_128 listo para imprimir
│   ├── recepcion/                 # Vistas del módulo de recepción
│   │   ├── control_llegada.php    # Pantalla auto-enfocada para lector de código de barras o digitación
│   │   └── citas_dia.php          # Agenda semanal interactiva con selector AJAX de Lunes a Domingo
│   ├── medico/                    # Vistas del módulo médico
│   │   ├── agenda.php             # Agenda de citas asignadas al especialista en sesión
│   │   ├── atencion.php           # Ficha clínica: historial previo, diagnóstico obligatorio y prescripción
│   │   └── receta_imprimible.php  # Hoja oficial de receta médica con código de barras y firma
│   └── reportes/                  # Vistas de auditoría y reportes institucionales
│       ├── cabecera_reporte.php   # Cabecera superior institucional unificada (#0F4C81)
│       ├── citas.php              # Reporte interactivo de citas médicas con enlace a Dompdf
│       ├── historial_paciente.php # Consulta de expediente clínico por paciente con enlace a Dompdf
│       ├── reporte_dompdf.php     # Controlador de renderizado y streaming oficial de PDF con Dompdf
│       └── dompdf/                # Librería Dompdf empaquetada con sus fuentes y autoloaders
├── database/                      # Scripts SQL para despliegue y datos iniciales
│   ├── schema.sql                 # Estructura DDL: tablas, llaves primarias, foráneas e índices optimizados
│   ├── triggers.sql               # Disparadores de sincronización automática entre usuarios y clientes
│   └── seed.sql                   # Datos semilla: especialistas, usuarios de prueba, citas y recetas demo
├── assets/                        # Recursos estáticos del sistema
│   ├── logo.png                   # Logotipo oficial de SmartMed Web
│   ├── css/
│   │   └── estilos.css            # Hoja de estilos unificada (paleta clínica, componentes y @media print)
│   ├── js/
│   │   ├── validaciones.js        # Filtro de solo dígitos en tiempo real para cédulas y teléfonos
│   │   ├── paciente.js            # Lógica AJAX de horarios libres y renderizado del ticket
│   │   ├── recepcion.js           # Enfoque persistente del escáner y actualización semanal AJAX
│   │   ├── medico.js              # Interacción de agenda y prescripción farmacológica
│   │   └── JsBarcode.all.min.js   # Librería para generación de códigos de barra CODE_128
│   └── img/
│       ├── logo.png               # Logotipo secundario en mapa de bits
│       └── logo.svg               # Isotipo vectorial de la cruz médica
└── README.md                      # Documentación técnica completa para sustentación
```

---

## 3. Tabla de Equivalencias (Java NetBeans Swing → PHP MVC Web)

Para facilitar la sustentación y defensa ante el jurado, se detalla la equivalencia directa entre los componentes clásicos de escritorio y su implementación web actual:

| Componente Escritorio (Java / Swing) | Módulo Web Equivalente (PHP / JS) | Propósito y Funcionamiento |
| :--- | :--- | :--- |
| `ConexionBD.java` | `config/conexion.php` | Conexión a la base de datos `consultorio_dbw` mediante `PDO`. |
| `Sesion.java` | `config/sesion.php` | Control de sesión activa, verificación de roles (`requerirRol`) y datos de usuario. |
| `Login.java` | `controllers/AuthController.php`<br>`views/login.php` | Autenticación de credenciales seguras con `password_verify` y diseño Split-Screen. |
| *(Nueva funcionalidad web)* | `views/registro.php` | Registro autónomo para que pacientes creen su cuenta sin intervención de recepción. |
| `ClienteDAO.java` | `models/Cliente.php` | Consultas SQL de pacientes por cédula, ID interno o ID de usuario. |
| `EspecialistaDAO.java` | `models/Especialista.php` | Consultas y listados de médicos especialistas activos y sus áreas médicas. |
| `CitaDAO.java` | `models/Cita.php` | Transacciones de reserva de turnos, verificación de horas ocupadas y estados. |
| `VistaPaciente.java` | `controllers/PacienteController.php`<br>`views/paciente/agendar.php` | Selección interactiva de médico, fecha y chips de horas libres en tiempo real. |
| `TicketDigita.java` | `views/paciente/ticket.php` | Comprobante digital con código de barras CODE_128 generado con `JsBarcode`. |
| `VistaRecepcion.java` | `controllers/RecepcionController.php`<br>`views/recepcion/control_llegada.php` | Lectura de ticket por escáner láser para cambio de estado a *En Proceso*. |
| *(Nueva funcionalidad web)* | `views/recepcion/citas_dia.php` | Tablero semanal de Lunes a Domingo con carga dinámica por AJAX. |
| `VistaMedico.java` | `controllers/MedicoController.php`<br>`views/medico/agenda.php` | Agenda médica filtrada exclusivamente para el médico especialista en sesión. |
| `PanelHistorialMedico.java` | `models/HistorialClinico.php`<br>`views/medico/atencion.php` | Expediente clínico, registro del diagnóstico médico y bloqueo en citas finalizadas. |
| *(Nueva funcionalidad web)* | `models/Receta.php`<br>`views/medico/receta_imprimible.php` | Prescripción farmacológica en texto libre y emisión de receta electrónica con código. |
| `GestorReportes.java` | `controllers/ReporteController.php`<br>`views/reportes/reporte_dompdf.php` | Auditoría de citas e historial clínico con exportación oficial a PDF vía Dompdf. |

---

## 4. Arquitectura MVC y Flujo por Rol

El sistema utiliza un patrón **Front Controller** donde todas las peticiones convergen en `index.php`:

```text
[ Cliente (Navegador Web) ]
           │
           ▼
     [ index.php ] (Front Controller)
           │
   Verifica sesión y rol (config/sesion.php)
           │
           ▼
 [ Controlador (controllers/) ]
      │               ▲
      │ Llama método  │ Retorna datos
      ▼               │
  [ Modelo (models/) ] ──► [ MySQL (consultorio_dbw) ]
      │
      ▼
 [ Vista (views/) ] ──► [ HTML + CSS + JS Renderizado ]
```

### Mapa Completo de Rutas en `index.php`

* **Autenticación:**
  * `index.php?ruta=login` → `AuthController::login()`
  * `index.php?ruta=registro` → `AuthController::registro()`
  * `index.php?ruta=logout` → `AuthController::logout()`
* **Portal del Paciente:**
  * `index.php?ruta=paciente/agendar` → `PacienteController::agendar()`
  * `index.php?ruta=paciente/citas` → `PacienteController::misCitas()`
  * `index.php?ruta=paciente/ticket&id={id}` → `PacienteController::verTicket()`
  * `index.php?ruta=paciente/cancelar&id={id}` → `PacienteController::cancelar()`
  * `index.php?ruta=paciente/recetas` → `PacienteController::misRecetas()`
  * `index.php?ruta=api/horarios-ocupados` → `PacienteController::horariosOcupados()` *(Respuesta JSON vía AJAX)*
* **Portal de Recepción:**
  * `index.php?ruta=recepcion/control` → `RecepcionController::controlLlegada()`
  * `index.php?ruta=recepcion/validar` → `RecepcionController::validarTicket()`
  * `index.php?ruta=recepcion/ingresar` → `RecepcionController::ingresarCita()`
  * `index.php?ruta=recepcion/citas_dia` → `RecepcionController::citasDia()`
  * `index.php?ruta=api/citas-dia` → `RecepcionController::citasDiaAjax()` *(Respuesta JSON vía AJAX)*
* **Portal del Médico Especialista:**
  * `index.php?ruta=medico/agenda` → `MedicoController::agenda()`
  * `index.php?ruta=medico/atencion&id={id}` → `MedicoController::atencion()`
  * `index.php?ruta=medico/receta&id={id}` → `MedicoController::recetaForm()`
  * `index.php?ruta=medico/receta_guardar` → `MedicoController::guardarReceta()`
  * `index.php?ruta=medico/receta_imprimible&id={id}` → `MedicoController::verReceta()`
* **Módulo de Reportes Oficiales y Auditoría:**
  * `index.php?ruta=reportes/citas` → `ReporteController::citas()`
  * `index.php?ruta=reportes/historial` → `ReporteController::historial()`
  * `index.php?ruta=reportes/pdf` → Carga directa de `views/reportes/reporte_dompdf.php`

---

## 5. Reglas de Negocio Implementadas

### A. Franjas Horarias de Agendamiento
El sistema implementa dos turnos diarios divididos en intervalos fijos de **30 minutos**:
* **Turno de Mañana:** Desde las **07:00** hasta las **12:00** (`07:00`, `07:30`, `08:00`, `08:30`, `09:00`, `09:30`, `10:00`, `10:30`, `11:00`, `11:30`, `12:00`).
* **Turno de Tarde:** Desde las **13:30** hasta las **19:00** (`13:30`, `14:00`, `14:30`, `15:00`, `15:30`, `16:00`, `16:30`, `17:00`, `17:30`, `18:00`, `18:30`, `19:00`).
* **Control de Disponibilidad Asíncrona:** Cuando el paciente selecciona fecha y médico, el frontend consulta `api/horarios-ocupados` vía AJAX; los turnos ya reservados por ese médico quedan deshabilitados en pantalla impidiendo el solapamiento de turnos.

### B. Ciclo de Vida y Estados de la Cita Médica

```text
[ PACIENTE AGENDAR ] 
         │ 
         ▼
    ( Pendiente ) ──(Paciente cancela cita)──► [ Cancelada ] (Libera horario en agenda)
         │
(Recepción escanea ticket o valida código)
         │
         ▼
   ( En Proceso ) ──(Médico guarda atención)──► [ Finalizada ] (Cita cerrada y bloqueada)
         │                                            │
         └────────► [ SE EMITE RECETA MÉDICA ] ◄──────┘
```

* **`Pendiente`:** Cita creada por el paciente. Puede ser cancelada libremente por el paciente antes de su realización (libera el cupo en la agenda). No permite emisión de recetas.
* **`En Proceso`:** Marcada cuando recepción valida el ticket digital (vía lector de código de barras o digitación manual). Indica que el paciente está físicamente en la sala de espera y habilita al médico para iniciar la consulta.
* **`Finalizada`:** Marcada cuando el especialista atiende al paciente y registra el diagnóstico clínico. La cita queda cerrada definitivamente.
* **`Cancelada`:** Borrado lógico (`cancelada_en = NOW()`) que preserva la auditoría histórica y libera inmediatamente el horario para otros pacientes.

### C. Consulta Clínica y Prescripción en Texto Libre
* **Diagnóstico Obligatorio:** En `views/medico/atencion.php`, el médico debe ingresar de manera obligatoria la evaluación médica del paciente.
* **Prescripción Integrada en Texto Libre:** El médico dispone de un área de prescripción farmacológica flexible donde puede redactar medicamentos y posología en texto libre (ej. *"Tomar Paracetamol 500mg cada 8 horas por 3 días"*), además de indicaciones generales o cuidados dietéticos.
* **Almacenamiento Automatizado:** El controlador procesa el texto, registra la receta en la tabla `recetas` y genera un código oficial único (ej. `REC-20261110-1001`), descomponiendo además los medicamentos en `receta_medicamentos`.

### D. Bloqueo Estricto de Edición en Citas Finalizadas
* Cuando una cita adquiere el estado **`Finalizada`**, la interfaz médica de atención se transforma en **modo de solo lectura**.
* Se despliega un banner de seguridad con ícono de candado (🔒): *"No se puede editar porque esta cita ya finalizó"*.
* Todos los campos de texto (`diagnostico`, `notas`, `prescripcion`, `indicaciones`) se bloquean mediante el atributo `disabled`, impidiendo alteraciones no autorizadas y garantizando la integridad legal del historial médico.
* Permanece habilitado el botón para consultar y reimprimir la receta médica emitida.

### E. Filtro por Rol en Agenda y Reportes
* **Médico Especialista:** Al ingresar a **Mi Agenda** o al **Reporte de Citas**, el sistema detecta su cédula en sesión y filtra la consulta SQL por su `id_especialista`. El médico **únicamente visualiza sus citas asignadas**, protegiendo la confidencialidad entre especialistas.
* **Recepción / Administración:** Tienen una visión **global** de todas las citas del consultorio para control de flujo de pacientes, asistencia y auditoría general.
* **Seguridad en Recetas:** Un paciente solo puede visualizar o imprimir recetas médicas asociadas a su propia cédula/ID de cliente. Si intenta acceder al ID de una receta ajena, el sistema bloquea la petición y redirige con mensaje de acceso denegado.

### F. Generación Oficial de Reportes en PDF con Dompdf
* La generación de reportes oficiales se centraliza en `views/reportes/reporte_dompdf.php`.
* Se eliminaron los botones antiguos de impresión de ventana (`window.print`) en los reportes, consolidando a **Dompdf** como la única vía oficial para exportar:
  * **Reporte de Citas:** Filtrable por rango de fechas (desde/hasta) y restringido automáticamente al médico en sesión si aplica.
  * **Expediente de Historial Clínico:** Filtrable por cédula de paciente con histórico de diagnósticos y evoluciones.
  * **Recetas Médicas Oficiales:** Con datos del paciente, médico, medicamentos, indicaciones y código de emisión.
* Los PDFs se generan con cabecera azul marino institucional (`#0F4C81`), tipografía Unicode `DejaVu Sans` y streaming directo al navegador.

---

## 6. Instalación y Configuración Local en XAMPP

### Paso 1: Ubicación de los Archivos
Clone o copie la carpeta del proyecto dentro del directorio raíz de su servidor web Apache:
```text
C:\xampp\htdocs\Consultorio Medico Web\
```

### Paso 2: Iniciar Servicios
Abra el panel de control de **XAMPP** e inicie los módulos:
* **Apache** (servidor web)
* **MySQL** (motor de base de datos)

### Paso 3: Importar la Base de Datos en phpMyAdmin
Ingrese a `http://localhost/phpmyadmin/` y en la pestaña **Importar** cargue los tres archivos de la carpeta `database/` en este **orden estricto**:
1. `database/schema.sql` (crea la base de datos `consultorio_dbw`, tablas con llaves foráneas e índices).
2. `database/triggers.sql` (crea el trigger `trg_sincronizar_usuario_after_insert` para sincronización de perfiles).
3. `database/seed.sql` (inserta los especialistas, cuentas demo de prueba y citas de ejemplo).

### Paso 4: Verificación de Conexión
El archivo `config/conexion.php` viene preconfigurado con los valores predeterminados de XAMPP:
```php
$servidor = '127.0.0.1';
$baseDatos = 'consultorio_dbw';
$usuario = 'root';
$clave = '';
```
*(Modifique estos valores únicamente si su instalación local de MySQL utiliza contraseña o un puerto diferente).*

### Paso 5: Ingreso al Sistema
Abra su navegador web e ingrese a la dirección:
```text
http://localhost/Consultorio%20Medico%20Web/index.php
```

---

## 7. Cuentas de Acceso Demo para la Sustentación

Todas las contraseñas del entorno de pruebas se encuentran encriptadas mediante `PASSWORD_BCRYPT`:

| Rol del Usuario | Usuario | Contraseña | Especialidad / Identificación | Cédula | Ruta de Entrada |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Recepción** | `recepcion` | `recepcion123` | Recepción Central | `1700000002` | `index.php?ruta=recepcion/control` |
| **Médico 1** | `dr_gomez` | `medico123` | Dr. Roberto Gómez &bull; *Cardiología* | `1711223344` | `index.php?ruta=medico/agenda` |
| **Médico 2** | `dra_ortiz` | `medico123` | Dra. Carmen Ortiz &bull; *Medicina General* | `1722334455` | `index.php?ruta=medico/agenda` |
| **Médico 3** | `dra_paredes` | `medico123` | Dra. Valeria Paredes &bull; *Pediatría* | `1733445566` | `index.php?ruta=medico/agenda` |
| **Médico 4** | `dr_andrade` | `medico123` | Dr. Esteban Andrade &bull; *Traumatología* | `1744556677` | `index.php?ruta=medico/agenda` |
| **Paciente Demo** | `juan_perez` | `paciente123` | Juan Pérez (Paciente) | `1799887766` | `index.php?ruta=paciente/agendar` |

> **Nota:** Para crear pacientes adicionales, cualquier persona puede utilizar el botón **"¿No tiene cuenta? Regístrese aquí"** en la pantalla de inicio de sesión (`index.php?ruta=registro`).

---

## 8. Flujo de Trabajo y Ramas Git

El repositorio sigue un esquema estructurado de control de versiones con **Conventional Commits**:

* **`main`:** Rama principal que contiene la versión base inicial estable del proyecto. Permanece intacta para preservar el estado de referencia original.
* **`develop`:** Rama activa de desarrollo e integración continua donde se implementan todas las funcionalidades, mejoras de experiencia de usuario, auditorías de seguridad y correcciones.

### Estándar de Mensajes de Commit Utilizado:
* `feat(...)`: Incorporación de nuevas funciones (ej. agendamiento interactivo, reportes Dompdf).
* `fix(...)`: Corrección de errores o validaciones de sesión (ej. resolución de funciones indefinidas y permisos).
* `style(...)`: Mejoras estéticas y diseño de interfaz (ej. pantalla Split-Screen y cabeceras unificadas).
* `refactor(...)`: Reestructuración de código sin alterar el comportamiento funcional.
* `docs(...)`: Actualizaciones en documentación técnica y manuales (`README.md`).
