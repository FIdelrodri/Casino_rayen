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