-- =============================================================================
-- schema.sql
-- Para qué sirve: Define la base de datos consultorio_dbw, sus tablas, llaves e índices.
-- Trabaja con: MySQL / MariaDB en XAMPP.
-- Equivale en NetBeans a: Los scripts SQL embebidos en ConexionBD.java y los DAOs.
-- =============================================================================

-- Crear la base de datos limpia con codificación UTF-8 para admitir tildes y ñ
DROP DATABASE IF EXISTS consultorio_dbw;
CREATE DATABASE consultorio_dbw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE consultorio_dbw;

-- 1. Tabla de usuarios del sistema (cuentas de acceso)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('paciente', 'medico', 'recepcion', 'admin') NOT NULL,
    cedula VARCHAR(10) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabla de clientes (pacientes del consultorio)
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

-- 3. Tabla de especialistas (médicos disponibles)
-- La cédula es UNIQUE para evitar médicos duplicados al sincronizar usuarios
CREATE TABLE IF NOT EXISTS especialistas (
    id_especialista INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(120) NOT NULL,
    especialidad VARCHAR(100) NOT NULL DEFAULT 'Medicina General',
    cedula VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 4. Tabla de citas médicas
CREATE TABLE IF NOT EXISTS citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_especialista INT NOT NULL,
    fecha_cita DATETIME NOT NULL,
    codigo_barras VARCHAR(50) NOT NULL UNIQUE,
    estado ENUM('Pendiente', 'En Proceso', 'Finalizada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelada_en DATETIME NULL,
    INDEX idx_citas_especialista_fecha (id_especialista, fecha_cita),
    INDEX idx_citas_estado (estado),
    INDEX idx_citas_codigo (codigo_barras),
    CONSTRAINT fk_cita_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON UPDATE CASCADE,
    CONSTRAINT fk_cita_especialista FOREIGN KEY (id_especialista) REFERENCES especialistas(id_especialista) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Tabla de historial clínico (una entrada por cita realizada)
CREATE TABLE IF NOT EXISTS historial_clinico (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_cita INT NOT NULL UNIQUE,
    diagnostico TEXT NOT NULL,
    notas TEXT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_cita FOREIGN KEY (id_cita) REFERENCES citas(id_cita) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Tabla de recetas médicas (NUEVA función web)
CREATE TABLE IF NOT EXISTS recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_cita INT NOT NULL,
    id_cliente INT NOT NULL,
    id_especialista INT NOT NULL,
    codigo_receta VARCHAR(50) NOT NULL UNIQUE,
    indicaciones TEXT NULL,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_receta_cita FOREIGN KEY (id_cita) REFERENCES citas(id_cita) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_receta_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_receta_especialista FOREIGN KEY (id_especialista) REFERENCES especialistas(id_especialista) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 7. Tabla de medicamentos por receta (NUEVA función web)
CREATE TABLE IF NOT EXISTS receta_medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_receta INT NOT NULL,
    medicamento VARCHAR(150) NOT NULL,
    dosis VARCHAR(100) NOT NULL,
    frecuencia VARCHAR(100) NOT NULL,
    duracion VARCHAR(100) NOT NULL,
    via_administracion VARCHAR(50) NULL,
    CONSTRAINT fk_med_receta FOREIGN KEY (id_receta) REFERENCES recetas(id_receta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
