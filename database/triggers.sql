-- =============================================================================
-- triggers.sql
-- Para qué sirve: Sincroniza automáticamente la creación de usuarios con las
--                 tablas de clientes o especialistas según el rol asignado.
-- Trabaja con: Tabla 'usuarios', 'clientes' y 'especialistas'.
-- =============================================================================

USE consultorio_dbw;

DELIMITER $$

-- Trigger: se ejecuta luego de insertar un usuario en la tabla usuarios
DROP TRIGGER IF EXISTS trg_sincronizar_usuario_after_insert$$

CREATE TRIGGER trg_sincronizar_usuario_after_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    -- Si el usuario registrado es un paciente, se asegura de registrarlo en la tabla clientes
    IF NEW.rol = 'paciente' THEN
        INSERT INTO clientes (cedula, nombre, telefono)
        VALUES (NEW.cedula, NEW.nombre, NEW.telefono)
        ON DUPLICATE KEY UPDATE
            nombre = NEW.nombre,
            telefono = NEW.telefono;
            
    -- Si el usuario registrado es un médico, se registra en especialistas con especialidad por defecto
    -- Si ya existe un especialista con esa cédula, no se duplica ni se sobrescribe su especialidad
    ELSEIF NEW.rol = 'medico' THEN
        INSERT INTO especialistas (nombre_completo, especialidad, cedula)
        VALUES (NEW.nombre, 'Medicina General', NEW.cedula)
        ON DUPLICATE KEY UPDATE
            nombre_completo = NEW.nombre;
    END IF;
END$$

DELIMITER ;

