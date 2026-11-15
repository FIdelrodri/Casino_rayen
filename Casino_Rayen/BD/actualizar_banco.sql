USE Casino_rayen;

ALTER TABLE Banco ADD UNIQUE KEY banco_unico (id_dueño_usu);

CREATE OR REPLACE VIEW vista_banco AS
SELECT id_dueño_usu AS id_usuario, id_banco, Monto FROM Banco;

CREATE OR REPLACE VIEW vista_banco_movimientos AS
SELECT b.id_dueño_usu AS id_usuario, i.id_hs, i.Monto_trasaccionado, i.Fecha
FROM Banco_Ingresos i JOIN Banco b ON b.id_banco = i.Banco;

DROP PROCEDURE IF EXISTS admin_borrar_usuario;
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
        DELETE FROM codigos_canjeados WHERE id_usuario = p_id_usuario;
        DELETE FROM Banco_Ingresos WHERE Banco IN (SELECT id_banco FROM Banco WHERE id_dueño_usu = p_id_usuario);
        DELETE FROM Banco WHERE id_dueño_usu = p_id_usuario;
        DELETE FROM Usuario         WHERE id_usuario   = p_id_usuario;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS depositar_banco;
DELIMITER $$
CREATE PROCEDURE depositar_banco(
    IN p_id_usuario INT,
    IN p_monto BIGINT
)
BEGIN
    DECLARE v_saldo BIGINT;
    DECLARE v_id_banco INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_monto IS NULL OR p_monto < 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El monto tiene que ser mayor a 0';
    END IF;

    START TRANSACTION;

    SELECT COALESCE(Saldo, 0) INTO v_saldo FROM Usuario WHERE id_usuario = p_id_usuario FOR UPDATE;
    IF v_saldo IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario no existe';
    END IF;
    IF v_saldo < p_monto THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No tenés saldo suficiente';
    END IF;

    SELECT id_banco INTO v_id_banco FROM Banco WHERE id_dueño_usu = p_id_usuario FOR UPDATE;
    IF v_id_banco IS NULL THEN
        INSERT INTO Banco (id_dueño_usu, Monto) VALUES (p_id_usuario, 0);
        SET v_id_banco = LAST_INSERT_ID();
    END IF;

    UPDATE Banco SET Monto = COALESCE(Monto, 0) + p_monto WHERE id_banco = v_id_banco;
    INSERT INTO Banco_Ingresos (Banco, Monto_trasaccionado) VALUES (v_id_banco, p_monto);
    CALL actualizar_saldo(p_id_usuario, -p_monto);

    COMMIT;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS retirar_banco;
DELIMITER $$
CREATE PROCEDURE retirar_banco(
    IN p_id_usuario INT,
    IN p_monto BIGINT
)
BEGIN
    DECLARE v_usuario INT;
    DECLARE v_id_banco INT;
    DECLARE v_monto BIGINT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_monto IS NULL OR p_monto < 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El monto tiene que ser mayor a 0';
    END IF;

    START TRANSACTION;

    SELECT id_usuario INTO v_usuario FROM Usuario WHERE id_usuario = p_id_usuario FOR UPDATE;
    IF v_usuario IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario no existe';
    END IF;

    SELECT id_banco, COALESCE(Monto, 0) INTO v_id_banco, v_monto FROM Banco WHERE id_dueño_usu = p_id_usuario FOR UPDATE;
    IF v_id_banco IS NULL OR v_monto < p_monto THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No tenés tanto dinero en el banco';
    END IF;

    UPDATE Banco SET Monto = Monto - p_monto WHERE id_banco = v_id_banco;
    INSERT INTO Banco_Ingresos (Banco, Monto_trasaccionado) VALUES (v_id_banco, -p_monto);
    CALL actualizar_saldo(p_id_usuario, p_monto);

    COMMIT;
END$$
DELIMITER ;

