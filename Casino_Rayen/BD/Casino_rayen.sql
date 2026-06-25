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
    Saldo DECIMAL(18,2) DEFAULT 0.00,
    Rol INT,
    Estatus INT
);

CREATE TABLE Banco (
    id_banco INT AUTO_INCREMENT PRIMARY KEY,
    id_dueño_usu INT,
    Monto DECIMAL(18,2) DEFAULT 0.00
);

CREATE TABLE Banco_Ingresos (
    id_hs INT AUTO_INCREMENT PRIMARY KEY,
    Banco INT,
    Monto_trasaccionado DECIMAL(18,2) NOT NULL,
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
    Monto_apostado DECIMAL(18,2) NOT NULL,
    Monto_recibido DECIMAL(18,2) NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE historial (
    id_hs INT AUTO_INCREMENT PRIMARY KEY,
    id_histo_usu INT,
    Nuevo_monto DECIMAL(18,2) NOT NULL,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE codigos (
    id_codigo INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    premio DECIMAL(18,2) NOT NULL,
    cantidad INT NOT NULL
);
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
    IN p_monto_cambio DECIMAL(18,2)
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
