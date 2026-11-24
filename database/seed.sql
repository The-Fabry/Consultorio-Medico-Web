-- =============================================================================
-- seed.sql
-- Para qué sirve: Inserta datos iniciales de prueba (especialistas, usuarios demo,
--                 pacientes y citas en distintos estados para verificar flujos).
-- Trabaja con: consultorio_dbw.
-- =============================================================================

USE consultorio_dbw;

-- 1. Insertar especialistas médicos
INSERT INTO especialistas (id_especialista, nombre_completo, especialidad, cedula) VALUES
(1, 'Dr. Roberto Gomez', 'Cardiologia', '1711223344'),
(2, 'Dra. Carmen Ortiz', 'Medicina General', '1722334455'),
(3, 'Dra. Valeria Paredes', 'Pediatria', '1733445566'),
(4, 'Dr. Esteban Andrade', 'Traumatologia', '1744556677')
ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo), especialidad = VALUES(especialidad);

-- 2. Insertar usuarios del sistema
-- Contraseñas hasheadas con BCRYPT (clave: medico123 para médicos, recepcion123 para recepción, paciente123 para pacientes)
INSERT INTO usuarios (id_usuario, usuario, password, rol, cedula, nombre, telefono, activo) VALUES
(1, 'recepcion', '$2y$10$W29.UQJok5GGSqvb/17WTesuD8OvPvYowFJBlR1w0QrWn6jwACexu', 'recepcion', '1700000002', 'Recepcion Principal', '0990000002', 1),
(2, 'juan_perez', '$2y$10$eftn.kpuo.I23V6ZnjqDQu0.3ClRi2ubOaQ5MXEfSOkObthkoTHxO', 'paciente', '1799887766', 'Juan Perez', '0987654321', 1),
-- Un usuario médico por cada especialista del seed:
(3, 'dr_gomez', '$2y$10$WueNFitTFZxrywRYG8mgouim17T4Ml1FXsr14LBDll0BIUnJ1d2ni', 'medico', '1711223344', 'Dr. Roberto Gomez', '0990000003', 1),
(4, 'dra_ortiz', '$2y$10$WueNFitTFZxrywRYG8mgouim17T4Ml1FXsr14LBDll0BIUnJ1d2ni', 'medico', '1722334455', 'Dra. Carmen Ortiz', '0990000004', 1),
(5, 'dra_paredes', '$2y$10$WueNFitTFZxrywRYG8mgouim17T4Ml1FXsr14LBDll0BIUnJ1d2ni', 'medico', '1733445566', 'Dra. Valeria Paredes', '0990000005', 1),
(6, 'dr_andrade', '$2y$10$WueNFitTFZxrywRYG8mgouim17T4Ml1FXsr14LBDll0BIUnJ1d2ni', 'medico', '1744556677', 'Dr. Esteban Andrade', '0990000006', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 3. Insertar datos en clientes (paciente demo y adicionales)
INSERT INTO clientes (id_cliente, cedula, nombre, telefono) VALUES
(1, '1799887766', 'Juan Perez', '0987654321'),
(2, '1712345678', 'Maria Lopez', '0991234567'),
(3, '1798765432', 'Carlos Mendoza', '0982345678')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 4. Citas de prueba para demostrar los diferentes estados y flujos
-- Cita 1: Pendiente (lista para escanear en recepción o para que el paciente la cancele)
INSERT INTO citas (id_cita, id_cliente, id_especialista, fecha_cita, codigo_barras, estado, creado_en) VALUES
(1, 1, 1, DATE_ADD(NOW(), INTERVAL 2 DAY), 'CITA-1799887766-4821', 'Pendiente', NOW()),
-- Cita 2: En Proceso (el paciente ya llegó a recepción y el médico puede atenderlo)
(2, 2, 1, NOW(), 'CITA-1712345678-1930', 'En Proceso', NOW()),
-- Cita 3: Finalizada (ya atendida con diagnóstico previo)
(3, 3, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), 'CITA-1798765432-8412', 'Finalizada', DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE estado = VALUES(estado);

-- 5. Historial clínico para la cita finalizada
INSERT INTO historial_clinico (id_historial, id_cita, diagnostico, notas, fecha_registro) VALUES
(1, 3, 'Hipertension arterial grado 1 en control.', 'Paciente refiere leve cefalea matutina. Se sugiere continuar dieta baja en sodio.', DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE diagnostico = VALUES(diagnostico);

-- 6. Receta de prueba para la cita finalizada
INSERT INTO recetas (id_receta, id_cita, id_cliente, id_especialista, codigo_receta, indicaciones, fecha_emision) VALUES
(1, 3, 3, 1, 'REC-20261110-1001', 'Tomar los medicamentos diariamente despues del desayuno con abundante agua.', DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE codigo_receta = VALUES(codigo_receta);

-- Medicamentos para la receta de prueba
INSERT INTO receta_medicamentos (id_receta, medicamento, dosis, frecuencia, duracion, via_administracion) VALUES
(1, 'Losartan Potasico', '50 mg', 'Cada 24 horas', '30 dias', 'Oral'),
(1, 'Aspirina Protect', '100 mg', 'Cada 24 horas despues de la comida principal', '30 dias', 'Oral');

