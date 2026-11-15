DROP DATABASE IF EXISTS Casino_rayen;
CREATE DATABASE Casino_rayen;
USE Casino_rayen;

CREATE TABLE ROLES (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_rol VARCHAR(50) NOT NULL
);

CREATE TABLE Estatus (
    id_estatus INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estatus VARCHAR(50) NOT NULL
);

CREATE TABLE Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_usuario VARCHAR(50) NOT NULL unique,
    Contraseña VARCHAR(255) NOT NULL,
    Nombre VARCHAR(50) NULL,
    Apellido VARCHAR(50) NULL,
    Dni VARCHAR(20) NULL,
    Correo_eletronico VARCHAR(100) NULL,
    Saldo BIGINT DEFAULT 0,
    Rol INT default 1,
    Estatus INT
);

CREATE TABLE Banco (
    id_banco INT AUTO_INCREMENT PRIMARY KEY,
    id_dueño_usu INT unique,
    Monto BIGINT DEFAULT 0
);

CREATE TABLE Banco_Ingresos (
    id_hs INT AUTO_INCREMENT PRIMARY KEY,
    Banco INT,
    Monto_trasaccionado BIGINT NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Juego (
    id_juego INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_juego VARCHAR(100) NOT NULL
);

CREATE TABLE Historial_Juego (
    id_jugada INT AUTO_INCREMENT PRIMARY KEY,
    id_histo_usu INT,
    juego INT,
    Monto_apostado BIGINT NOT NULL,
    Monto_recibido BIGINT NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE historial (
    id_hs INT AUTO_INCREMENT PRIMARY KEY,
    id_histo_usu INT,
    Nuevo_monto BIGINT NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE codigos (
    id_codigo INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL unique,
    premio BIGINT NOT NULL,
    cantidad INT NOT NULL
);

CREATE TABLE codigos_canjeados (
    id_canje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_codigo INT NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    unique (id_usuario, id_codigo)
);

CREATE VIEW vista_banco AS
SELECT id_dueño_usu AS id_usuario, id_banco, Monto FROM Banco;

CREATE VIEW vista_banco_movimientos AS
SELECT b.id_dueño_usu AS id_usuario, i.id_hs, i.Monto_trasaccionado, i.Fecha
FROM Banco_Ingresos i JOIN Banco b ON b.id_banco = i.Banco;
delimiter $$
create procedure crearcuenta(
    in p_nombre_usuario varchar(50),
    in p_nombre varchar(50),
    in p_apellido varchar(50),
    in p_dni varchar(20),
    in p_correo_electronico varchar(100),    
    in p_contraseña varchar(20)

)
begin
insert into Usuario(Nombre_usuario, Nombre, Apellido, Dni, Correo_eletronico, Contraseña)
values (p_nombre_usuario, p_nombre, p_apellido, p_dni, p_correo_electronico, p_contraseña);
end
$$ delimiter ;
DELIMITER //

CREATE PROCEDURE verificar_usuario(
    IN nom_user VARCHAR(50), 
    IN pass_user VARCHAR(255)
)
BEGIN
    SELECT * FROM usuario 
    WHERE Nombre_usuario = nom_user 
      AND contraseña = pass_user;
END 
// DELIMITER ;
DELIMITER $$

CREATE PROCEDURE actualizar_saldo(
    IN p_id_usuario INT,
    IN p_monto_cambio BIGINT
)
BEGIN
    UPDATE Usuario SET Saldo = Saldo + p_monto_cambio WHERE id_usuario = p_id_usuario;
    INSERT INTO historial (id_histo_usu, Nuevo_monto, Fecha) SELECT id_usuario, Saldo, NOW() FROM Usuario WHERE id_usuario = p_id_usuario;
END$$

DELIMITER ;
DELIMITER $$
CREATE PROCEDURE admin_modificar_saldo(
    IN p_id_admin INT,
    IN p_id_usuario INT,
    IN p_nuevo_saldo BIGINT
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

DELIMITER $$
CREATE PROCEDURE admin_crear_codigo(
    IN p_id_admin  INT,
    IN p_codigo    VARCHAR(50),
    IN p_premio    BIGINT,
    IN p_cantidad  INT
)
BEGIN
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        IF EXISTS (SELECT 1 FROM codigos WHERE codigo = p_codigo) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe un código con ese nombre';
        END IF;
        INSERT INTO codigos (codigo, premio, cantidad)
        VALUES (p_codigo, p_premio, p_cantidad);
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;
DELIMITER $$
CREATE PROCEDURE admin_borrar_codigo(
    IN p_id_admin INT,
    IN p_id_codigo INT
)
BEGIN
    DECLARE v_rol INT;
    SELECT Rol INTO v_rol FROM Usuario WHERE id_usuario = p_id_admin;
    IF v_rol = 2 THEN
        DELETE FROM codigos_canjeados WHERE id_codigo = p_id_codigo;
        DELETE FROM codigos WHERE id_codigo = p_id_codigo;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE canjear_codigo(
    IN p_id_usuario INT,
    IN p_codigo VARCHAR(50)
)
BEGIN
    DECLARE v_id_codigo INT;
    DECLARE v_premio BIGINT;
    DECLARE v_cantidad INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    IF NOT EXISTS (SELECT 1 FROM Usuario WHERE id_usuario = p_id_usuario) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario no existe';
    END IF;

    SELECT id_codigo, premio, cantidad INTO v_id_codigo, v_premio, v_cantidad
    FROM codigos WHERE codigo = p_codigo LIMIT 1 FOR UPDATE;

    IF v_id_codigo IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El código no existe';
    END IF;
    IF v_cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El código ya no tiene usos disponibles';
    END IF;
    IF EXISTS (SELECT 1 FROM codigos_canjeados WHERE id_usuario = p_id_usuario AND id_codigo = v_id_codigo) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya canjeaste este código';
    END IF;

    INSERT INTO codigos_canjeados (id_usuario, id_codigo) VALUES (p_id_usuario, v_id_codigo);
    CALL actualizar_saldo(p_id_usuario, v_premio);

    IF v_cantidad = 1 THEN
        DELETE FROM codigos WHERE id_codigo = v_id_codigo;
    ELSE
        UPDATE codigos SET cantidad = cantidad - 1 WHERE id_codigo = v_id_codigo;
    END IF;

    COMMIT;
    SELECT v_premio AS premio;
END$$
DELIMITER ;

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

insert into ROLES (nombre_rol) values 
('Usuario'),('Admin');