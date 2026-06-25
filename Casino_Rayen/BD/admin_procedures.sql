-- ============================================================
-- STORED PROCEDURES - Panel de Administración Casino Rayen
-- Agregar estos procedures a la BD Casino_rayen
-- ============================================================

USE Casino_rayen;

-- -------------------------------------------------------
-- 1. Modificar saldo de un usuario
-- -------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE admin_modificar_saldo(
    IN p_id_admin INT,
    IN p_id_usuario INT,
    IN p_nuevo_saldo DECIMAL(18,2)
)
BEGIN
    -- Verificar que quien ejecuta es admin (rol 2)
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        UPDATE Usuario SET Saldo = p_nuevo_saldo WHERE id_usuario = p_id_usuario;
        INSERT INTO historial (id_histo_usu, Nuevo_monto, Fecha)
        VALUES (p_id_usuario, p_nuevo_saldo, NOW());
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;

-- -------------------------------------------------------
-- 2. Borrar cuenta de usuario
-- -------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE admin_borrar_usuario(
    IN p_id_admin INT,
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        DELETE FROM historial       WHERE id_histo_usu = p_id_usuario;
        DELETE FROM Historial_Juego WHERE id_histo_usu = p_id_usuario;
        DELETE FROM Usuario         WHERE id_usuario   = p_id_usuario;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;

-- -------------------------------------------------------
-- 3. Crear código promocional
-- -------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE admin_crear_codigo(
    IN p_id_admin  INT,
    IN p_codigo    VARCHAR(50),
    IN p_premio    DECIMAL(18,2),
    IN p_cantidad  INT
)
BEGIN
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        INSERT INTO codigos (codigo, premio, cantidad)
        VALUES (p_codigo, p_premio, p_cantidad);
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;

-- -------------------------------------------------------
-- 4. Borrar código promocional
-- -------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE admin_borrar_codigo(
    IN p_id_admin INT,
    IN p_id_codigo INT
)
BEGIN
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        DELETE FROM codigos WHERE id_codigo = p_id_codigo;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;
